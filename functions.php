<?php

use function PHPSTORM_META\type;

function connect($database){
    $servername = "localhost";
    $username = "root"; // boss
    $password = "MySQLpassword";

    $connection = mysqli_connect($servername, $username, $password, $database);
    
    if (!$connection) {
        echo "Connection failed: " ; 
        die("Connection failed: " . mysqli_connect_error());
    } else{
        // echo '<h1>---Connected---</h1><br><br>';
        // echo '<h1>---Connected<sub>/[' . $raw_name . ']</sub>---</h1><br><br>';
        return $connection;
    }
}

function primary_key($tableName, $connection){
    // Query the database schema to retrieve primary key information
    $query = "SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'";
    $result = $connection->query($query);
    // Check if the query was successful
    if ($result) {
        // Fetch the primary key column(s)
        $primaryKeyColumns = array();
        while ($row = $result->fetch_assoc()) {
        $primaryKeyColumns[] = $row['Column_name'];
        }
    }
    return $primaryKeyColumns[0];
}

function none_null_col($tableName, $connection){
    $query = "SHOW COLUMNS FROM `$tableName` WHERE `Null` = 'NO';";
    $result = $connection->query($query);
    if ($result) {
        $nonNullColumns = array();
        while ($row = $result->fetch_assoc()) {
        $nonNullColumns[] = $row['Field'];
        }
    }
    return $nonNullColumns;
}


function insert($table, $dict, $connection)
{
    $column_names = "";
    $placeholders = "";
    $types = "";
    $values = array();
    foreach ($dict as $key => $value) {
        $sql = "SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $table, $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if ($row) {
            $column_name = $key;
            $data_type = $row['DATA_TYPE'];
            if ($column_names != "") {
                $column_names .= ", ";
                $placeholders .= ", ";
                $types .= "";
            }
            $column_names .= $column_name;
            $placeholders .= "?";
            $types .= $data_type[0];
                $values[] = $value;
        }
    }

    $sql = "INSERT INTO $table ($column_names) VALUES ($placeholders)";
    // echo $types;
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Insert operation succedded.", "<br><br>";
    } else {
        echo "Insert operation failed", "<br><br>";
    }
    mysqli_stmt_close($stmt);
}
?>
