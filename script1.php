<?php



// Create a dummy request object with the file ID
$request = new stdClass();
$request->id = 6; // Replace with the actual file ID

// Instantiate the aspect
$aspect = new \App\Aspects\FileCheckDeletAspect();

// Execute the `beforeDelete` aspect method
$response = $aspect->beforeDelete($request);

// Perform further operations if the lock was acquired successfully
if ($response->getStatusCode() === 200) {
    // Perform the deletion process
    // ...

    // Execute the `afterDelete` aspect method
    $response = $aspect->afterDelete($request, $response);

    // Output the final response
    echo $response->getContent();
} else {
    // Output the lock acquisition failure message
    echo $response->getContent();
}
