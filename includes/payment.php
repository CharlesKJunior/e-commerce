<?php
// includes/payment.php
require_once 'config.php';

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Process MTN Mobile Money payment
     */
    public function processMTNMobileMoney($phone, $amount, $reference) {
        $conn = $this->db->getConnection();
        
        // MTN API endpoint (sandbox/production)
        $url = MTN_ENVIRONMENT === 'production' 
            ? 'https://api.mtn.com/v1/mobile-money/payments'
            : 'https://sandbox.mtn.com/v1/mobile-money/payments';
        
        // Prepare request data
        $data = [
            'amount' => $amount,
            'currency' => 'UGX',
            'externalId' => $reference,
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $this->formatPhoneNumber($phone, 'mtn')
            ],
            'payerMessage' => 'Payment for order ' . $reference,
            'payeeNote' => 'Thank you for your purchase'
        ];
        
        // Make API request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->getMTNAccessToken(),
            'Content-Type: application/json',
            'X-Reference-Id: ' . uniqid()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 202) {
            $result = json_decode($response, true);
            
            // Save payment record
            $sql = "INSERT INTO payments (order_id, payment_method, amount, currency, 
                    transaction_id, reference, phone_number, network, status) 
                    VALUES (?, 'mtn_mobile', ?, 'UGX', ?, ?, ?, 'mtn', 'processing')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idsss", 
                $result['orderId'] ?? 0,
                $amount,
                $result['transactionId'] ?? '',
                $reference,
                $phone
            );
            $stmt->execute();
            
            return [
                'success' => true,
                'transaction_id' => $result['transactionId'] ?? '',
                'reference' => $reference,
                'message' => 'Payment request sent to your phone'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to initiate MTN payment: ' . $response
            ];
        }
    }
    
    /**
     * Process Airtel Money payment
     */
    public function processAirtelMoney($phone, $amount, $reference) {
        $conn = $this->db->getConnection();
        
        // Airtel API endpoint (sandbox/production)
        $url = AIRTEL_ENVIRONMENT === 'production'
            ? 'https://openapi.airtel.africa/merchant/v1/payments/'
            : 'https://openapiuat.airtel.africa/merchant/v1/payments/';
        
        // Prepare request data
        $data = [
            'reference' => $reference,
            'subscriber' => [
                'country' => 'UG',
                'currency' => 'UGX',
                'msisdn' => $this->formatPhoneNumber($phone, 'airtel')
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => 'UG',
                'currency' => 'UGX',
                'id' => $reference
            ]
        ];
        
        // Make API request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Country: UG',
            'X-Currency: UGX',
            'Authorization: Bearer ' . $this->getAirtelAccessToken()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            if ($result['status']['code'] === '200') {
                // Save payment record
                $sql = "INSERT INTO payments (order_id, payment_method, amount, currency, 
                        transaction_id, reference, phone_number, network, status) 
                        VALUES (?, 'airtel_money', ?, 'UGX', ?, ?, ?, 'airtel', 'processing')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("idsss", 
                    $result['data']['transaction']['id'] ?? 0,
                    $amount,
                    $result['data']['transaction']['airtel_money_id'] ?? '',
                    $reference,
                    $phone
                );
                $stmt->execute();
                
                return [
                    'success' => true,
                    'transaction_id' => $result['data']['transaction']['airtel_money_id'] ?? '',
                    'reference' => $reference,
                    'message' => 'Payment request sent to your phone'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Failed to initiate Airtel payment'
        ];
    }
    
    /**
     * Generic mobile money processor
     */
    public function processMobileMoney($orderId, $network, $phone, $amount, $reference) {
        switch ($network) {
            case 'mtn':
                return $this->processMTNMobileMoney($phone, $amount, $reference);
            case 'airtel':
                return $this->processAirtelMoney($phone, $amount, $reference);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported mobile network'
                ];
        }
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus($transactionId, $network) {
        if ($network === 'mtn') {
            return $this->checkMTNPaymentStatus($transactionId);
        } elseif ($network === 'airtel') {
            return $this->checkAirtelPaymentStatus($transactionId);
        }
        
        return ['success' => false, 'message' => 'Unknown network'];
    }
    
    /**
     * Verify webhook signature (for payment callbacks)
     */
    public function verifyWebhook($signature, $payload, $secret) {
        $calculatedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($signature, $calculatedSignature);
    }
    
    /**
     * Handle payment callback from gateway
     */
    public function handleCallback($data, $network) {
        $conn = $this->db->getConnection();
        
        if ($network === 'mtn') {
            $transactionId = $data['transactionId'] ?? '';
            $status = $data['status'] ?? '';
            
            if ($status === 'SUCCESSFUL') {
                // Update payment status
                $sql = "UPDATE payments SET status = 'success', paid_at = NOW() 
                        WHERE transaction_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $transactionId);
                $stmt->execute();
                
                // Update order status
                $sql = "UPDATE orders o 
                        JOIN payments p ON o.id = p.order_id 
                        SET o.payment_status = 'completed', o.status = 'confirmed' 
                        WHERE p.transaction_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $transactionId);
                $stmt->execute();
                
                // Send confirmation email
                $this->sendPaymentConfirmation($transactionId);
                
                return true;
            }
        }
        
        return false;
    }
    
    private function getMTNAccessToken() {
        // Get MTN API access token (implement token caching)
        // This is a simplified version
        return MTN_API_KEY; // In reality, you'd make an OAuth request
    }
    
    private function getAirtelAccessToken() {
        // Get Airtel API access token
        return AIRTEL_API_KEY; // In reality, you'd make an OAuth request
    }
    
    private function formatPhoneNumber($phone, $network) {
        // Format phone number for API
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strpos($phone, '256') === 0) {
            $phone = substr($phone, 3);
        } elseif (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }
        
        return $phone;
    }
    
    private function sendPaymentConfirmation($transactionId) {
        // Send email confirmation
        // Implementation depends on your email setup
    }
    
    private function checkMTNPaymentStatus($transactionId) {
        // Check MTN payment status via API
        // Implementation needed
        return ['success' => true, 'status' => 'completed'];
    }
    
    private function checkAirtelPaymentStatus($transactionId) {
        // Check Airtel payment status via API
        // Implementation needed
        return ['success' => true, 'status' => 'completed'];
    }
}