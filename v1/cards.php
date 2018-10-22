<?php

    /*
        cardkingdom-api - cards.py
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

    # Array for cards
    $cards = [];

    # Create connection
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    # Charset to handle unicode
    $conn->set_charset('utf8mb4');
    mysqli_set_charset($conn, 'utf8mb4');

    # Check connection
    if ($conn) {

        # UUID direct search
        if(!empty($_GET['uuid']))
        {
            # Card UUID
            $card_uuid = build_str(clean_str($conn, $_GET['uuid']));

            # Prepare and run query
            $sql_query = "SELECT * FROM sets_cards WHERE uuid = ".$card_uuid." ORDER BY setId, uuid LIMIT 10 OFFSET ".($page*10);

            # Return cards matching query
            response(200, "ok", get_cards($sql_query, $conn, $cards));
        }
        else
        {
            # Card name and set ID search
            if(!empty($_GET['name']) && !empty($_GET['set']))
            {
                # Card name
                $card_name = strtoupper(clean_str($conn, $_GET['name']));

                # Set ID
                $set_id = strtoupper(build_str(clean_str($conn, $_GET['set'])));

                # Prepare and run query
                $sql_query = "SELECT * FROM sets_cards WHERE upper(name) LIKE '%".$card_name."%' AND setId = ".$set_id." ORDER BY setId, uuid LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_cards($sql_query, $conn, $cards));
            }
            # Card name search
            elseif(!empty($_GET['name']))
            {
                # Card name
                $card_name = strtoupper(clean_str($conn, $_GET['name']));

                # Prepare and run query
                $sql_query = "SELECT * FROM sets_cards WHERE upper(name) LIKE '%".$card_name."%' ORDER BY setId, uuid LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_cards($sql_query, $conn, $cards));
            }
            # Set ID search
            elseif(!empty($_GET['set']))
            {
                # Set ID
                $set_id = strtoupper(build_str(clean_str($conn, $_GET['set'])));

                # Prepare and run query
                $sql_query = "SELECT * FROM sets_cards WHERE setId = ".$set_id." ORDER BY setId, uuid LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_cards($sql_query, $conn, $cards));
            }
            # Get all cards (limited to page)
            else
            {
                # Prepare and run query
                $sql_query = "SELECT * FROM sets_cards ORDER BY setId, uuid LIMIT 10 OFFSET ".($page*10);

                # Return cards matching query
                response(200, "ok", get_cards($sql_query, $conn, $cards));
            }
        }
    }
    else
    {
        # Return error
        response(500, "Unable to connect to the database", NULL);
    }

    # Execute query, push color identities to array and return it
    function get_coloridentity($sql_conn, $card_uuid, $coloridentities)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_coloridentity WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, identity";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru coloridentities
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build coloridentity data
                $coloridentity = $row["identity"];

                # Push card to coloridentity array
                array_push($coloridentities, $coloridentity);
            }
        }

        # Return array of JSON coloridentities
        return $coloridentities;
    }

    # Execute query, push colors to array and return it
    function get_colors($sql_conn, $card_uuid, $colors)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_colors WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, color";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru colors
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build color data
                $color = $row["color"];

                # Push card to colors array
                array_push($colors, $color);
            }
        }

        # Return array of JSON colors
        return $colors;
    }

    # Execute query, push foreigndata to array and return it
    function get_foreigndata($sql_conn, $card_uuid, $foreigndata)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_foreigndata WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, foreigndata, language";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru foreigndata
            while ($row = mysqli_fetch_assoc($result))
            {

                # foreigndata data
                $foreigndata_multiverseid =(int)$row["multiverseId"];

                # foreigndata card gatherer URL
                $foreigndata_image_url = "";
                if ($foreigndata_multiverseid <> 0)
                {
                    $foreigndata_image_url = "http://gatherer.wizards.com/Handlers/Image.ashx?multiverseid=".$foreigndata_multiverseid."&type=card";
                }

                # Build foreigndata data
                $card_foreigndata = [
                    "flavorText" => $row["flavorText"],
                    "language" => $row["language"],
                    "multiverseId" => $foreigndata_multiverseid,
                    "name" => $row["name"],
                    "text" => $row["text"],
                    "type" => $row["type"],
                    #--------------------------------
                    "imageUrl" => $foreigndata_image_url
                ];

                # Push card to foreigndata array
                array_push($foreigndata, $card_foreigndata);
            }
        }

        # Return array of JSON foreigndata
        return $foreigndata;
    }

    # Execute query, push legalities to array and return it
    function get_legalities($sql_conn, $card_uuid)
    {
        # Legalities empty structure
        $legalities = [
            "1v1" => "",
            "brawl" => "",
            "commander" => "",
            "duel" => "",
            "frontier" => "",
            "legacy" => "",
            "modern" => "",
            "standard" => "",
            "vintage" => ""
        ];

        # Prepare query
        $sql_query = "SELECT * FROM cards_legalities WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru legalities
            while ($row = mysqli_fetch_assoc($result))
            {
                # Update key
                $legalities[$row["format"]] = $row["legality"];
            }
        }

        # Return array of JSON legalities
        return $legalities;
    }

    # Execute query, push names to array and return it
    function get_names($sql_conn, $card_uuid, $names)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_names WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, nameId";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru names
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build names data
                $name = $row["name"];

                # Push card to names array
                array_push($names, $name);
            }
        }

        # Return array of JSON names
        return $names;
    }

    # Execute query, push printings to array and return it
    function get_printings($sql_conn, $card_uuid, $printings)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_printings WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, printing";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru printings
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build printings data
                $printing = $row["printing"];

                # Push card to printings array
                array_push($printings, $printing);
            }
        }

        # Return array of JSON printings
        return $printings;
    }

    # Execute query, push rulings to array and return it
    function get_rulings($sql_conn, $card_uuid, $rulings)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_rulings WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, ruling";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru rulings
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build rulings data
                $card_rulings = [
                    "date" => $row["date"],
                    "text" => $row["text"]
                ];

                # Push card to rulings array
                array_push($rulings, $card_rulings);
            }
        }

        # Return array of JSON rulings
        return $rulings;
    }

    # Execute query, push subtypes to array and return it
    function get_subtypes($sql_conn, $card_uuid, $subtypes)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_subtypes WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, subtype";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru subtypes
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build subtype data
                $subtype = $row["subtype"];

                # Push card to subtypes array
                array_push($subtypes, $subtype);
            }
        }

        # Return array of JSON subtypes
        return $subtypes;
    }

    # Execute query, push supertypes to array and return it
    function get_supertypes($sql_conn, $card_uuid, $supertypes)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_supertypes WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, supertype";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru supertypes
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build supertype data
                $supertype = $row["supertype"];

                # Push card to supertypes array
                array_push($supertypes, $supertype);
            }
        }

        # Return array of JSON supertypes
        return $supertypes;
    }

    # Execute query, push types to array and return it
    function get_types($sql_conn, $card_uuid, $types)
    {

        # Prepare query
        $sql_query = "SELECT * FROM cards_types WHERE uuid = ".build_str($card_uuid)." ORDER BY uuid, type";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru types
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build type data
                $type = $row["type"];

                # Push card to supertypes array
                array_push($types, $type);
            }
        }

        # Return array of JSON types
        return $types;
    }

    # Execute query, push prices to array and return it
    function get_prices($sql_conn, $card_uuid)
    {
        # Empty structure
        $prices = [
            "cardkingdom.com" => []
        ];

        # Current source
        $source = build_str("cardkingdom.com");

        # Prepare query
        $sql_query = "SELECT * FROM cards_prices WHERE uuid = ".build_str($card_uuid)." AND source = ".$source." ORDER BY uuid ASC, source ASC, date DESC LIMIT 1";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru prices
            while ($row = mysqli_fetch_assoc($result))
            {
                # Build all four prices (near mint, excellent, very good and good)
                $current_price = [$row["nm_price"], $row["ex_price"], $row["vg_price"], $row["g_price"]];
                
                # Update array
                $prices[$row["source"]] = $current_price;

            }
        }

        # Return array of JSON prices
        return $prices;
    }

    # Execute query, push cards to array and return it
    function get_cards($sql_query, $sql_conn, $cards)
    {

        # Arrays for card data
        $coloridentity = [];
        $colors = [];
        $foreigndata = [];
        $printings = [];
        $rulings = [];
        $subtypes = [];
        $supertypes = [];
        $types = [];

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # Loop thru cards
            while ($row = mysqli_fetch_assoc($result))
            {

                # Card primary data
                $card_uuid = $row["uuid"];
                $set_id = $row["setId"];
                $card_multiverseid =(int)$row["multiverseId"];

                # Card gatherer URL
                $card_image_url = "";
                if ($card_multiverseid <> 0)
                {
                    $card_image_url = "http://gatherer.wizards.com/Handlers/Image.ashx?multiverseid=".$card_multiverseid."&type=card";
                }

                # Build card data
                $card = [
                    "artist" => $row["artist"],
                    "borderColor" => $row["borderColor"],
                    "colorIdentity" => get_coloridentity($sql_conn, $card_uuid, $coloridentity),
                    # colorIndicator?
                    "colors" => get_colors($sql_conn, $card_uuid, $colors),
                    "convertedManaCost" => floatval($row["convertedManaCost"]),
                    "flavorText" => $row["flavorText"],
                    "isFoilOnly" => (bool)$row["isFoilOnly"],
                    "isFoilOnly" => (bool)$row["isFoilOnly"],
                    "foreignData" => get_foreigndata($sql_conn, $card_uuid, $foreigndata),
                    "hasFoil" => (bool)$row["hasFoil"],
                    "hasNonFoil" => (bool)$row["hasNonFoil"],
                    "isOnlineOnly" => (bool)$row["isOnlineOnly"],
                    "isOversized" => (bool)$row["isOversized"],
                    "isReserved" => (bool)$row["isReserved"],
                    "layout" => $row["layout"],
                    "legalities" => get_legalities($sql_conn, $card_uuid), # Not array!
                    "loyalty" => $row["loyalty"],
                    "manaCost" => $row["manaCost"],
                    "multiverseId" => $card_multiverseid,
                    "name" => $row["name"],
                    "names" => get_names($sql_conn, $card_uuid, $colors),
                    "number" => $row["number"],
                    "originalText" => $row["originalText"],
                    "originalType" => $row["originalType"],
                    "printings" => get_printings($sql_conn, $card_uuid, $printings),
                    "power" => $row["power"],
                    "rarity" => $row["rarity"],
                    "rulings" => get_rulings($sql_conn, $card_uuid, $rulings),
                    "subtypes" => get_subtypes($sql_conn, $card_uuid, $subtypes),
                    "supertypes" => get_supertypes($sql_conn, $card_uuid, $supertypes),
                    "text" => $row["text"],
                    "toughness" => $row["toughness"],
                    "type" => $row["type"],
                    "types" => get_types($sql_conn, $card_uuid, $types),
                    "uuid" => $card_uuid,
                    "watermark" => $row["watermark"],
                    #--------------------------------
                    "set" => $set_id,
                    "imageUrl" => $card_image_url,
                    "prices" => get_prices($sql_conn, $card_uuid),
                ];

                # Push card to cards array
                array_push($cards, $card);
            }
        }

        # Return array of JSON cards
        return $cards;
    }
?>