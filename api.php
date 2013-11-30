<?php
    // #1 include json library and connect to mysql db
    include("JSON.php"); // depends on where
    $con = mysql_connect("studentdb.gl.umbc.edu","akone1","Z00ninj@");
    if(!$con){die('Could not connect: ' . mysql_error());}
    mysql_select_db("akone1", $con);

    // #2 parse the request url
    $url = $_SERVER['REQUEST_URI']; //get whole url
    $http = $_SERVER['REQUEST_METHOD']; // get http method from request header
    $p = parse_url( $url ); // http://php.net/manual/en/function.parse-url.php
    $q=explode('/', $p['query']); // http://www.w3schools.com/php/func_string_explode.asp
    $c=count($q); // length of array - lowest count is 2 - http://www.w3schools.com/php/func_array_count.asp

    // #3 all legal combinations of http method and url type for a rest api
    if ($q[1]!="rafflers") { // lockdown the mysql table
        header("Content-type: text/json", TRUE, 404); // http://php.net/manual/en/function.header.php
        echo("{error: Incorrect table name}");
    }
    else if($c<3) { // no id in the url - e.g. http://host/index.php?/users - do not use a trailing slash
        if ($http=='GET')  { // GET method etc.
            $result = mysql_query("SELECT * FROM $q[1]"); // sql query using table name from url
            $json=array(); // create a blank array
            while($row = mysql_fetch_assoc($result)) {$json[]=$row; } // add each row to array
            mysql_close($con); // close the db connection
            if(!$result) { // $result returns false if the insert fails
                header("Content-type: text/json", TRUE, 500); // 500 is server error of any kind
                echo("{error: Select failed}");
            } else {
                header("Content-type: text/json", TRUE, 200);
                $a = json_encode($json); // transform array to json using library function
                echo $a; // return the json string
            }
        }
        else if ($http=='POST')  {
            // http://www.w3schools.com/sql/sql_insert.asp - autoincrement
            // we are ignoring sql injection here - http://www.w3schools.com/php/func_mysql_real_escape_string.asp
            $data = json_decode(file_get_contents('php://input'), true); // http://php.net/manual/en/function.json-decode.php
            $name=$data->name; 
            $winner=$data->winner;
            $result = mysql_query("INSERT INTO $q[1] (ID, name, winner) VALUES (NULL, '$name', '$winner')"); 
            $id = mysql_insert_id();
            mysql_close($con); 
            if(!$result) { // $result returns false if the insert fails
                header("Content-type: text/json", TRUE, 500);
                echo("{error: Insert failed}");
            } else {
                header("Content-type: text/json", TRUE, 201);
                 $data->id=$id; // transform array to json using library function
                 echo json_encode($data); // return the json string
            } // 201 means 'created'
        }
        else {
            header("Content-type: text/json", TRUE, 500);
            echo("{error: Improper verb use}");
        }
    }
    else // has id ($q[2]) in the url - note count will be 3 - e.g. http://host/index.php?/users/3
    {
        if ($http=='GET') {
            $result = mysql_query("SELECT * FROM $q[1] where id = $q[2]"); // sql query using table name from url
            $json = mysql_fetch_assoc($result);
            mysql_close($con); // close the db connection
            if(!$result) { // $result returns false if the insert fails
                header("Content-type: text/json", TRUE, 500); // 500 is server error of any kind
                echo("{error: Select failed}");
            } else {
                header("Content-type: text/json", TRUE, 200);
                $a = json_encode($json); // transform array to json using library function
                echo $a; // return the json string
            }
        }
        else if ($http=='PUT') { // PUT and DELETE require ajax or curl
            // http://www.w3schools.com/sql/sql_update.asp - must send all fields even if changing only one
            $input = file_get_contents('php://input'); // there is no $_PUT in php
            $data = json_decode( $input , true); // http://php.net/manual/en/function.json-decode.php
            $name=$data->name; 
            $winner=$data->winner;
         
            $result = mysql_query("UPDATE $q[1] SET name='$name', winner='$winner'  WHERE ID=$q[2]"); 
            mysql_close($con); 
            if(!$result) { // $result returns false if the update fails
                header("Content-type: text/json", TRUE, 500);
                echo("{error: Update failed}");
            } else {header("Content-type: text/json", TRUE, 200); } 
        } 
        else if ($http=='DELETE') {
            // http://www.w3schools.com/sql/sql_delete.asp
            // you need to add this
            mysql_query("DELETE FROM $q[1] where id = $q[2]"); // sql query using table name from url
            $deleted_row_count = mysql_affected_rows();
            mysql_close($con); // close the db connection
            if($deleted_row_count == 1) {
                 header("Content-type: text/json", TRUE, 200); 
                 echo("{deleted: ${deleted_row_count}}");

            } else 
            {
                 // $result returns false if the delete fails
                header("Content-type: text/json", TRUE, 500); // 500 is server error of any kind
                echo("{error: Delete failed}");

            } 
        }
        else {
            header("Content-type: text/json", TRUE, 404);
            echo("{error: Improper verb use}");
        }
    }
?>