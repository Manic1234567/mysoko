<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

$trackingNumber = $_GET['tracking_number'] ?? '';

try {
    if (empty($trackingNumber)) {
        throw new Exception('Tracking number is required');
    }

    // Get shipment details
    $stmt = $pdo->prepare("SELECT * FROM logistics_requests WHERE tracking_number = ?");
    $stmt->execute([$trackingNumber]);
    $shipment = $stmt->fetch();

    if (!$shipment) {
        echo json_encode(['success' => false, 'message' => 'Shipment not found']);
        exit;
    }

    // Get tracking history
    $stmt = $pdo->prepare("SELECT * FROM logistics_tracking WHERE request_id = ? ORDER BY created_at DESC");
    $stmt->execute([$shipment['id']]);
    $history = $stmt->fetchAll();

    // Format response
    $response = [
        'success' => true,
        'shipment' => [
            'service' => $shipment['service'],
            'pickup' => $shipment['pickup'],
            'destination' => $shipment['destination'],
            'status' => $shipment['status'],
            'estimated_delivery' => 'Inakadiriwa', // You would calculate this
            'history' => array_map(function($item) {
                return [
                    'date' => date('M j, Y H:i', strtotime($item['created_at'])),
                    'description' => $item['description']
                ];
            }, $history)
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>