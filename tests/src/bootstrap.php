<?php

require_once 'app/autoload.php';

use Testify\Model\Database;

class ConfigTest {

    const SQL_USER = 'root';
    const SQL_HOST = 'localhost';
    const SQL_PASS = '';
    const SQL_DTB = 'testify_test';
}

function importSqlFile($pdo, $sqlFile) {
    try {

        $errorDetect = false;

        // Temporary variable, used to store current query
        $tmpLine = '';

        // Read in entire file
        $lines = file($sqlFile);

        // Loop through each line
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || trim($line) == '') {
                continue;
            }

            // Add this line to the current segment
            $tmpLine .= $line;

            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    // Perform the Query
                    $pdo->exec($tmpLine);
                } catch (\PDOException $e) {
                    echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
                    $errorDetect = true;
                }

                // Reset temp variable to empty
                $tmpLine = '';
            }
        }

        // Check if error is detected
        if ($errorDetect) {
            return false;
        }

    } catch (\Exception $e) {
        echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
        return false;
    }

    return true;
}


Database::getInstance(new ConfigTest);

importSqlFile(Database::getInstance()->getPDO(), 'tests/src/testify_test.sql');
