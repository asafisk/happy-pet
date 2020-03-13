<?php

require 'autoload.php';

use app\DataStore;

$db = DataStore::getInstance();

if (isset($_POST['submitUser'])) {
    unset($_POST['submitUser']);
    $db->insert($_POST, 'user');
}

if (isset($_POST['submitPet'])) {
    unset($_POST['submitPet']);
    $db->insert($_POST, 'pet');
}

if (isset($_POST['resetDatabase'])) {
    $db->resetDatabase();
    $db->initializeDatabase();
}

function printDataTable($data) {
    if (empty($data)) {
        ?>
        <tr>
            <td>No records to display</td>
        </td>
        <?php
        return null;
    }
    ?>
    <tr>
    <?php
    foreach($data[0] as $k => $v) {
        ?>
        <th><?php print($k); ?></th>
        <?php
    }
    ?>
    </tr>
    <?php
    foreach ($data as $row) {
        ?>
        <tr>
        <?php
        foreach ($row as $k => $v) {
            ?>
            <td><?php print($v); ?></td>
            <?php
        }
        ?>
        </tr>
        <?php
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Happy Pet</title>
        <style type="text/css">
            body {
                background: #eee;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 0.8em;
                margin: 0;
            }
            table {
                font-size: 0.9em;
            }
            .wrapper {
                padding: 10px;
                margin: 0 0 3em 0;
            }
            .dataGroup {
                display: inline;
                float: left;
                border: 4px solid #cde;
                background: #fff;
                padding: 10px;
                margin: 0 20px 20px 0;
            }
            .footer {
                position: fixed;
                width: 100%;
                bottom: 0;
                margin: 20px 0 0 0;
                padding: 1em 2em;
                background-color: #abc;
                text-align: center;
            }
            .clear {
                clear: both;
            }
        </style>
        
    </head>
    
    <body>
        <div class="wrapper">
            <h1>Happy Pet Dashboard</h1>
            
            <div class="dataGroup">
                <h2>Users</h2>
                <form action="index.php" method="post">
                    <p>
                        <label for="userName">User Name:</label>
                        <input id="userName" type="text" name="name" />
                    </p>
                    <p>
                        <input type="submit" name="submitUser" value="Submit User" />
                    </p>
                </form>
                <hr />
                <table border="1">
                    <?php
                    $data = $db->select(array('*'), 'user');
                    if (is_array($data)) {
                        printDataTable($data);
                    }
                    ?>
                </table>
            </div>
            
            
            <div class="dataGroup">
                <h2>Pets</h2>
                <form action="index.php" method="post">
                    <p>
                        <label for="userId">User ID:</label>
                        <input id="userId" type="text" name="user_id" />
                    </p>
                    <p>
                        <label for="petName">Pet Name:</label>
                        <input id="petName" type="text" name="pet_name" />
                    </p>
                    <p>
                        <label for="petType">Pet Type:</label>
                        <select id="petType" name="pet_type_id">
                            <option value="1">Dog</option>
                            <option value="2">Cat</option>
                            <option value="3">Bird</option>
                            <option value="4">Snake</option>
                            <option value="5">Spider</option>
                        </select>
                    </p>
                    <p>
                        <input type="submit" name="submitPet" value="Submit Pet" />
                    </p>
                </form>
                <hr />
                <table border="1">
                    <?php
                    $data = $db->select(array('*'), 'pet');
                    if (is_array($data)) {
                        printDataTable($data);
                    }
                    ?>
                </table>
            </div>
            
            <div class="dataGroup">
                <h2>Pet Types</h2>
                <table border="1">
                    <?php
                    $data = $db->select(array('*'), 'pet_type');
                    if (is_array($data)) {
                        printDataTable($data);
                    }
                    ?>
                </table>
            </div>
            
            <div class="dataGroup">
                <h2>Pet Events</h2>
                <table border="1">
                    <?php
                    $data = $db->select(array('*'), 'pet_event');
                    if (is_array($data)) {
                        printDataTable($data);
                    }
                    ?>
                </table>
            </div>
            <br class="clear" />
        </div>
        
        <div class="footer">
            <form action="index.php" method="post">
                <label>Reset the database:</label>
                <input type="submit" name="resetDatabase" id="resetButton" value="Delete All Data" />
            </form>
        </div>
        <script type="text/javascript">
            document.getElementById('resetButton').addEventListener('click', function() {
                return confirm('Sure?');
            });
        </script>
    </body>
</html>