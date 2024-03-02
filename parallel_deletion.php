<?php

require 'vendor/autoload.php';

use Symfony\Component\Process\Process;

// Create two separate deletion processes
$process1 = new Process(['php', 'script1.php']);
$process2 = new Process(['php', 'script2.php']);

// Start the processes concurrently
$process1->start();
$process2->start();

// Wait for both processes to finish
$process1->wait();
$process2->wait();

// Output the process outputs
echo "Process 1 output:\n";
echo $process1->getOutput() . "\n";

echo "Process 2 output:\n";
echo $process2->getOutput() . "\n";
