<?php

    /*
        cardkingdom-api - sets.py
        Contribute on https://github.com/cardkingdom-uy/cardkingdom-api
    */

    header("Content-Type:application/json");
    require "config.php";
    require "misc.php";

    # Get page
    $page = 0;
    if(!empty($_GET['page'])) {
        $page = (int)$_GET['page'];
    }

    # Array for sets
    $sets = [];

    # Create connection
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    # Charset to handle unicode
    $conn->set_charset('utf8mb4');
    mysqli_set_charset($conn, 'utf8mb4');

    # Check connection
    if ($conn) {

        # set ID direct search
        if(!empty($_GET['set']))
        {
            # Set ID
            $set_id = strtoupper(build_str(clean_str($conn, $_GET['set'])));

            # Prepare and run query
            $sql_query = "SELECT * FROM sets WHERE setId = ".$set_id." ORDER BY setId, name LIMIT 10 OFFSET ".($page*10);

            # Return cards matching query
            response(200, "ok", get_sets($sql_query, $conn, $sets));
        }
        else
        {
            # Set name search
            if(!empty($_GET['name']))
            {
                # Set name
                $set_name = strtoupper(clean_str($conn, $_GET['name']));

                # Prepare and run query
                $sql_query = "SELECT * FROM sets WHERE upper(name) LIKE '%".$set_name."%' ORDER BY setId, name LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_sets($sql_query, $conn, $sets));
            }
            # Get all sets (limited to page)
            else
            {
                # Prepare and run query
                $sql_query = "SELECT * FROM sets ORDER BY setId, name LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_sets($sql_query, $conn, $sets));
            }
        }
    }
    else
    {
        # Return error
        response(500, "Unable to connect to the database", NULL);
    }

    # Execute query, push equivs to array and return it
    function get_equivs($sql_conn, $set_id)
    {
        # Empty structure
        $equivs = [
            "cardkingdom.com" => 0
        ];

        # Current source
        $source = build_str("cardkingdom.com");

        # Prepare query
        $sql_query = "SELECT * FROM sets_equiv WHERE setId = ".build_str($set_id)." AND source = ".$source." ORDER BY setId, source";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru equivs
            while ($row = mysqli_fetch_assoc($result))
            {
                # Get equiv
                $equiv = (int)$row["equiv"];
                
                # Update array
                $equivs[$row["source"]] = $equiv;
            }
        }

        # Return array of JSON equivs
        return $equivs;
    }

    # Execute query, push sets to array and return it
    function get_sets($sql_query, $sql_conn, $sets)
    {

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru sets
            while ($row = mysqli_fetch_assoc($result))
            {

                # Set primary data
                $set_id = $row["setId"];

                # Build set data
                $set = [
                    "block" => $row["block"],
                    "code" => $row["code"],
                    "isOnlineOnly" => (bool)$row["isOnlineOnly"],
                    "mtgoCode" => $row["mtgoCode"],
                    "name" => $row["name"],
                    "releaseDate" => $row["releaseDate"],
                    "type" => $row["type"],
                    #--------------------------------
                    "set" => $set_id,
                    "equiv" => get_equivs($sql_conn, $set_id)
                ];

                # Push card to sets array
                array_push($sets, $set);
            }
        }

        # Return array of JSON sets
        return $sets;
    }
?>