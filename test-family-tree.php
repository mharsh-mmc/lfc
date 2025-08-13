<?php
/**
 * Family Tree Functionality Test Script
 * 
 * This script tests the key family tree functionalities:
 * 1. Central node creation
 * 2. Edge creation
 * 3. Node positioning
 * 4. Profile search and addition
 */

// Test configuration
$baseUrl = 'http://localhost:8000'; // Adjust to your Laravel app URL
$testUserId = 1; // Test with user ID 1

echo "🧪 Testing Family Tree Functionality\n";
echo "=====================================\n\n";

// Test 1: Get family tree (should create central node if none exists)
echo "1. Testing getTree endpoint...\n";
$response = testGetTree($baseUrl, $testUserId);
if ($response) {
    echo "✅ getTree endpoint working\n";
    $treeData = json_decode($response, true);
    echo "   - Nodes found: " . count($treeData['nodes']) . "\n";
    echo "   - Edges found: " . count($treeData['edges']) . "\n";
    
    // Check if central node exists
    $centralNode = null;
    foreach ($treeData['nodes'] as $node) {
        if ($node['relation'] === 'self') {
            $centralNode = $node;
            break;
        }
    }
    
    if ($centralNode) {
        echo "   - Central node found: " . $centralNode['profile']['name'] . "\n";
    } else {
        echo "   - ❌ Central node not found\n";
    }
} else {
    echo "❌ getTree endpoint failed\n";
}

echo "\n";

// Test 2: Search profiles
echo "2. Testing profile search...\n";
$response = testSearchProfiles($baseUrl, $testUserId, 'test');
if ($response) {
    echo "✅ Profile search working\n";
    $searchData = json_decode($response, true);
    echo "   - Profiles found: " . count($searchData['profiles']) . "\n";
} else {
    echo "❌ Profile search failed\n";
}

echo "\n";

// Test 3: Test edge creation (if we have at least 2 nodes)
echo "3. Testing edge creation...\n";
if (isset($treeData) && count($treeData['nodes']) >= 2) {
    $node1 = $treeData['nodes'][0]['id'];
    $node2 = $treeData['nodes'][1]['id'];
    
    $response = testCreateEdge($baseUrl, $testUserId, $node1, $node2);
    if ($response) {
        echo "✅ Edge creation working\n";
    } else {
        echo "❌ Edge creation failed\n";
    }
} else {
    echo "⚠️  Need at least 2 nodes to test edge creation\n";
}

echo "\n";

// Test 4: Test node update
echo "4. Testing node update...\n";
if (isset($treeData) && count($treeData['nodes']) > 0) {
    $nodeId = $treeData['nodes'][0]['id'];
    $response = testUpdateNode($baseUrl, $testUserId, $nodeId);
    if ($response) {
        echo "✅ Node update working\n";
    } else {
        echo "❌ Node update failed\n";
    }
} else {
    echo "⚠️  No nodes to test update\n";
}

echo "\n";
echo "🎯 Family Tree Test Complete!\n";

// Helper functions
function testGetTree($baseUrl, $userId) {
    $url = $baseUrl . "/api/profiles/{$userId}/familytree";
    return makeRequest($url, 'GET');
}

function testSearchProfiles($baseUrl, $userId, $query) {
    $url = $baseUrl . "/api/profiles/{$userId}/familytree/search?q=" . urlencode($query);
    return makeRequest($url, 'GET');
}

function testCreateEdge($baseUrl, $userId, $fromNode, $toNode) {
    $url = $baseUrl . "/api/profiles/{$userId}/familytree/edge";
    $data = [
        'from_node_id' => $fromNode,
        'to_node_id' => $toNode,
        'relationship_type' => 'family',
        'edge_type' => 'bezier'
    ];
    return makeRequest($url, 'POST', $data);
}

function testUpdateNode($baseUrl, $userId, $nodeId) {
    $url = $baseUrl . "/api/profiles/{$userId}/familytree/node/{$nodeId}";
    $data = [
        'x_position' => 500,
        'y_position' => 400
    ];
    return makeRequest($url, 'PATCH', $data);
}

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
    if ($method === 'POST' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "   - cURL Error: " . curl_error($ch) . "\n";
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return $response;
    } else {
        echo "   - HTTP Error: {$httpCode}\n";
        if ($response) {
            $errorData = json_decode($response, true);
            if (isset($errorData['error'])) {
                echo "   - Error: " . $errorData['error'] . "\n";
            }
        }
        return false;
    }
}
?>