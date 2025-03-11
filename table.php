<?php
$randomString = bin2hex(random_bytes(3));
require_once 'config.php';
require_once 'text.php';
global $connect;
//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'user'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE user (
        id varchar(500)  PRIMARY KEY,
        limit_usertest int(100) NOT NULL,
        roll_Status bool NOT NULL,
        Processing_value  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        Processing_value_one varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        Processing_value_tow varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        Processing_value_four varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        step varchar(1000) NOT NULL,
        description_blocking TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        number varchar(2000) NOT null ,
        Balance int(255) NOT null ,
        User_Status varchar(500) NOT NULL,
        pagenumber int(10) NOT NULL,
        message_count varchar(100) NOT NULL,
        last_message_time varchar(100) NOT NULL,
        affiliatescount varchar(100) NOT NULL,
        affiliates varchar(100) NOT NULL,
        verify varchar(50) NOT NULL,
        username varchar(1000) NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table User".mysqli_error($connect);
        }
    }
    else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'affiliatescount'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD affiliatescount VARCHAR(100)");
            $connect->query("UPDATE user SET affiliatescount = '0'");
            echo "The affiliatescount field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'verify'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD verify VARCHAR(50)");
            $connect->query("UPDATE user SET verify = '0'");
            echo "The verify field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'affiliates'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD affiliates VARCHAR(100)");
            $connect->query("UPDATE user SET affiliates = '0'");
            echo "The affiliates field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'message_count'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD message_count VARCHAR(100)");
            $connect->query("UPDATE user SET message_count = '0'");
            echo "The message_count field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'last_message_time'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD last_message_time VARCHAR(100)");
            $connect->query("UPDATE user SET last_message_time = '0'");
            echo "The last_message_time field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'Processing_value_four'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD Processing_value_four VARCHAR(100)");
            echo "The Processing_value_four field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'username'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD username VARCHAR(1000)");
            $connect->query("UPDATE user SET username = 'none'");
            echo "The username field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'Processing_value'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD Processing_value VARCHAR(1000)");
            $connect->query("UPDATE user SET Processing_value = 'none'");
            echo "The Processing_Value field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'Processing_value_tow'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD Processing_value_tow VARCHAR(1000)");
            $connect->query("UPDATE user SET Processing_value_tow = 'none'");
            echo "The Processing_value_tow field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'Processing_value_one'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD Processing_value_one VARCHAR(1000)");
            $connect->query("UPDATE user SET Processing_value_one = 'none'");
            echo "The Processing_value_one field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'Balance'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD Balance int(255)");
            $connect->query("UPDATE user SET Balance = '0'");
            echo "The Balance field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'number'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD number VARCHAR(1000)");
            $connect->query("UPDATE user SET number = 'none'");
            echo "The number field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'roll_Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD roll_Status bool");
            $connect->query("UPDATE user SET roll_Status = false");
            echo "The roll_Status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'description_blocking'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD description_blocking VARCHAR(5000)");
            echo "The description_blocking field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'User_Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD User_Status VARCHAR(500)");
            echo "The User_Status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM user LIKE 'pagenumber'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE user ADD pagenumber int(10)");
            echo "The page_number field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'help'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE help (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name_os varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        Media_os varchar(5000) NOT NULL,
        type_Media_os varchar(500) NOT NULL,
        Description_os TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table help".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'setting'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE setting (
        Bot_Status varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        help_Status varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        roll_Status varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        get_number varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        iran_number varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        NotUser varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        Channel_Report varchar(600)  NULL,
        limit_usertest_all varchar(600)  NULL,
        time_usertest varchar(600)  NULL,
        val_usertest varchar(600)  NULL,
        Extra_volume varchar(600)  NULL,
        namecustome varchar(100)  NULL,
        status_verify varchar(50)  NULL,
        removedayc varchar(100)  NULL,
        statuscategory varchar(100)  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table setting".mysqli_error($connect);
        }
        $active_bot_text = "1";
        $active_roll_text = "0";
        $active_phone_text = "0";
        $active_phone_iran_text = "0";
        $active_help = "0";
        $connect->query("INSERT INTO setting (Bot_Status,roll_Status,get_number,limit_usertest_all,time_usertest,val_usertest,help_Status,iran_number,NotUser,namecustome,removedayc,status_verify,statuscategory) VALUES ('$active_bot_text','$active_roll_text','$active_phone_text','1','1','100','$active_help','$active_phone_iran_text','0','0','1','0','1')");
    } else {
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'status_verify'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD status_verify VARCHAR(50)");
            $connect->query("UPDATE setting SET status_verify = '0'");
            echo "The status_verify field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'statuscategory'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD statuscategory VARCHAR(50)");
            $connect->query("UPDATE setting SET statuscategory = '1'");
            echo "The statuscategory field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'namecustome'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD namecustome VARCHAR(200)");
            echo "The configManual field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'removedayc'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD removedayc VARCHAR(100)");
            $connect->query("UPDATE setting SET removedayc = '1'");
            echo "The removedayc field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'Extra_volume'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD Extra_volume VARCHAR(200)");
            echo "The Extra_volume field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'NotUser'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD NotUser VARCHAR(200)");
            echo "The NotUser field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'iran_number'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD iran_number VARCHAR(200)");
            echo "The iran_number field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'get_number'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD get_number VARCHAR(200)");
            echo "The get_number field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'time_usertest'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD time_usertest VARCHAR(600)");
            echo "The time_usertest field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'val_usertest'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD val_usertest VARCHAR(600)");
            echo "The val_usertest field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'help_Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD help_Status VARCHAR(600)");
            echo "The help_Status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'limit_usertest_all'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD limit_usertest_all VARCHAR(600)");
            echo "The limit_usertest_all field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'Channel_Report'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD Channel_Report VARCHAR(200)");
            echo "The Channel_Report field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'Bot_Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD Bot_Status VARCHAR(200)");
            echo "The Bot_Status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM setting LIKE 'roll_Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE setting ADD roll_Status VARCHAR(200)");
            $connect->query("UPDATE setting SET roll_Status = '1'");
            echo "The roll_Status field was added ✅";
        }
        $settingsql = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM setting"));
        $active_phone_iran_text = "0";
        if(!isset($settingsql['iran_number'])){
            $stmt = $connect->prepare("UPDATE setting SET iran_number = ?");
            $stmt->bind_param("s", $active_phone_iran_text);
            $stmt->execute();
        }
        if(!isset($settingsql['NotUser'])){
            $stmt = $connect->prepare("UPDATE setting SET NotUser = ?");
            $text = "offnotuser";
            $stmt->bind_param("s", $text);
            $stmt->execute();
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}

//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'admin'");
    $table_exists = ($result->num_rows > 0);
    if ($table_exists) {
        $id_admin = mysqli_query($connect, "SELECT * FROM admin");
        while ($row = mysqli_fetch_assoc($id_admin)) {
            $admin_ids[] = $row['id_admin'];
        }
        if (!in_array($adminnumber, $admin_ids)) {
            $connect->query("INSERT INTO admin (id_admin) VALUES ('$adminnumber')");
            echo "table admin update✅</br>";
        }
    } else {
        $result =  $connect->query("CREATE TABLE admin (
        id_admin varchar(200) PRIMARY KEY NOT NULL)");
        $connect->query("INSERT INTO admin (id_admin) VALUES ('$adminnumber')");
        if (!$result) {
            echo "table admin".mysqli_error($connect);
        }  }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'channels'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result =  $connect->query("CREATE TABLE channels (
            link varchar(200) NOT NULL )");
        if (!$result) {
            echo "table channels".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//--------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'marzban_panel'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE marzban_panel (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name_panel varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        url_panel varchar(2000) NULL,
        username_panel varchar(200) NULL,
        password_panel varchar(200) NULL,
        status varchar(100) NULL,
        statusTest varchar(100) NULL,
        type varchar(200) NULL,
        linksubx varchar(500) NULL,
        inboundid varchar(200) NULL,
        MethodUsername varchar(900)  NULL,
        sublink varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        configManual varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        onholdstatus varchar(200) NULL,
        datelogin TEXT NULL,
        inbounds TEXT NULL,
        proxies TEXT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table marzban_panel".mysqli_error($connect);
        }
    }
    else{
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'datelogin'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD datelogin TEXT");
            echo "The datelogin field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'inbounds'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD inbounds TEXT");
            echo "The inbounds field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'proxies'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD proxies TEXT");
            echo "The proxies field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'statusTest'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD statusTest VARCHAR(100)");
            $connect->query("UPDATE marzban_panel SET statusTest = 'ontestshowpanel'");
            echo "The statusTest field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD status VARCHAR(100)");
            $connect->query("UPDATE marzban_panel SET status = 'activepanel'");
            echo "The status field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'onholdstatus'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD onholdstatus VARCHAR(100)");
            $connect->query("UPDATE marzban_panel SET onholdstatus = 'offonhold'");
            echo "The onholdstatus field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'sublink'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD sublink VARCHAR(200)");
            $connect->query("UPDATE marzban_panel SET sublink = 'onsublink'");
            echo "The sublink field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'configManual'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD configManual VARCHAR(200)");
            $connect->query("UPDATE marzban_panel SET configManual = 'offconfig'");
            echo "The configManual field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'MethodUsername'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD MethodUsername VARCHAR(900)");
            $connect->query("UPDATE marzban_panel SET MethodUsername = '{$textbotlang['users']['customidAndRandom']}'");
            echo "The MethodUsername field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'inboundid'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD inboundid VARCHAR(200)");
            echo "The inboundid field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'linksubx'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD linksubx VARCHAR(500)");
            echo "The linksubx field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM marzban_panel LIKE 'type'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE marzban_panel ADD type VARCHAR(200)");
            $connect->query("UPDATE marzban_panel SET type = 'marzban'");
            echo "The type field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'product'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE product (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_product varchar(200)  NULL,
        name_product varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        price_product varchar(2000) NULL,
        Volume_constraint varchar(2000) NULL,
        Location varchar(1000) NULL,
        Service_time varchar(200) NULL,
        Category varchar(600) NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table product".mysqli_error($connect);
        }
    }
    else{
        $Check_filde = $connect->query("SHOW COLUMNS FROM product LIKE 'Location'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE product ADD Location VARCHAR(1000)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM product LIKE 'Category'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE product ADD Category VARCHAR(600)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM product LIKE 'code_product'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE product ADD code_product VARCHAR(200)");
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'invoice'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE invoice (
        id_invoice varchar(200) PRIMARY KEY,
        id_user varchar(200) NULL,
        username varchar(2000) NULL,
        Service_location varchar(2000) NULL,
        time_sell varchar(2000) NULL,
        name_product varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        price_product varchar(2000) NULL,
        Volume varchar(2000) NULL,
        Service_time varchar(200) NULL,
        Status varchar(200) NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table invoice".mysqli_error($connect);
        }
    }
    else{
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'time_sell'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD time_sell VARCHAR(2000)");
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM invoice LIKE 'Status'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $result = $connect->query("ALTER TABLE invoice ADD Status VARCHAR(2000)");
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'Payment_report'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result =  $connect->query("CREATE TABLE Payment_report (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(200),
        id_order varchar(500),
        time varchar(200)  NULL,
        price varchar(400) NULL,
        dec_not_confirmed varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        Payment_Method varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
        payment_Status varchar(2000) NULL,
        invoice varchar(300) NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table Payment_report".mysqli_error($connect);
        }
    }
    else{
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'invoice'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD invoice VARCHAR(300)");
            echo "The invoice field was added ✅";
        }
        $Check_filde = $connect->query("SHOW COLUMNS FROM Payment_report LIKE 'Payment_Method'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE Payment_report ADD Payment_Method VARCHAR(1000)");
            echo "The Payment_Method field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'Discount'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result =  $connect->query("CREATE TABLE Discount (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code varchar(2000) NULL,
        price varchar(200) NULL)");
        if (!$result) {
            echo "table Discount".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {

    $result = $connect->query("SHOW TABLES LIKE 'Giftcodeconsumed'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result =  $connect->query("CREATE TABLE  Giftcodeconsumed (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code varchar(2000) NULL,
        id_user varchar(200) NULL)");
        if (!$result) {
            echo "table Giftcodeconsumed".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'textbot'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE textbot (
        id_text varchar(600) PRIMARY KEY NOT NULL,
        text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table textbot".mysqli_error($connect);
        }
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_start','{$textbotlang['users']['start']}') ");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_usertest','{$textbotlang['users']['usertest']['usertestbtn']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_Purchased_services','{$textbotlang['Admin']['Status']['title']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_support','{$textbotlang['users']['support']['title']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_help','{$textbotlang['users']['help']['title']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_bot_off','{$textbotlang['users']['botoff']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_roll','{$textbotlang['users']['RulesDescription']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_fq','{$textbotlang['users']['fqbtn']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_dec_fq','{$textbotlang['users']['fqDescription']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_account','{$textbotlang['users']['accountbtn']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_sell','{$textbotlang['users']['buybtn']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_Add_Balance','{$textbotlang['users']['add_balance']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_channel','{$textbotlang['users']['channeldosntjoin']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_Discount','{$textbotlang['users']['Discount']['titlebtn']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_Tariff_list','{$textbotlang['users']['pricelist']}')");
        $connect->query("INSERT INTO textbot (id_text,text) VALUES ('text_dec_Tariff_list','not set')");
    }
    else{
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_start','{$textbotlang['users']['start']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_usertest','{$textbotlang['users']['usertest']['usertestbtn']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_Purchased_services','{$textbotlang['Admin']['Status']['title']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_support','{$textbotlang['users']['support']['title']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_help','{$textbotlang['users']['help']['title']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_bot_off','{$textbotlang['users']['botoff']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_roll','{$textbotlang['users']['RulesDescription']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_fq','{$textbotlang['users']['fqbtn']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_dec_fq','{$textbotlang['users']['fqDescription']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_account','{$textbotlang['users']['accountbtn']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_sell','{$textbotlang['users']['buybtn']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_Add_Balance','{$textbotlang['users']['add_balance']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_channel','{$textbotlang['users']['channeldosntjoin']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_Discount','{$textbotlang['users']['Discount']['titlebtn']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_Tariff_list','{$textbotlang['users']['pricelist']}')");
        $connect->query("INSERT IGNORE INTO textbot (id_text,text) VALUES ('text_dec_Tariff_list','not set')");


    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
try {
    $result = $connect->query("SHOW TABLES LIKE 'PaySetting'");
    $table_exists = ($result->num_rows > 0);
    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE PaySetting (
        NamePay varchar(500) PRIMARY KEY NOT NULL,
        ValuePay TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table PaySetting".mysqli_error($connect);
        }
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('CartDescription','603700000000') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('Cartstatus','oncard') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('apinowpayment','0') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('nowpaymentstatus','offnowpayment') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('digistatus','offdigi') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('statusaqayepardakht','offaqayepardakht') ");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('merchant_id_aqayepardakht','0')");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_Payer_Account','0')");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_AccountID','0')");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_PassPhrase','0')");
        $connect->query("INSERT INTO PaySetting (NamePay,ValuePay) VALUES ('status_perfectmoney','offperfectmoney')");
    }
    else{
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('Cartstatus','oncard') ");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('CartDescription','603700000000') ");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('apinowpayment','0')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('nowpaymentstatus','offnowpayment')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('digistatus','offdigi')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('statusaqayepardakht','offaqayepardakht')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('merchant_id_aqayepardakht','0')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_Payer_Account','0')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_AccountID','0')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('perfectmoney_PassPhrase','0')");
        $connect->query("INSERT IGNORE INTO PaySetting (NamePay,ValuePay) VALUES ('status_perfectmoney','offperfectmoney')");



    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//----------------------- [ Discount ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'DiscountSell'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE DiscountSell (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codeDiscount varchar(1000)  NOT NULL,
        price varchar(200)  NOT NULL,
        limitDiscount varchar(500)  NOT NULL,
        usedDiscount varchar(500) NOT NULL,
        usefirst varchar(500) NOT NULL)");
        if (!$result) {
            echo "table DiscountSell".mysqli_error($connect);
        }
    }else{
        $Check_filde = $connect->query("SHOW COLUMNS FROM DiscountSell LIKE 'usefirst'");
        if (mysqli_num_rows($Check_filde) != 1) {
            $connect->query("ALTER TABLE DiscountSell ADD usefirst VARCHAR(500)");
            echo "The DiscountSell field was added ✅";
        }
    }
} catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//-----------------------------------------------------------------
try {
    $result = $connect->query("SHOW TABLES LIKE 'affiliates'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE affiliates (
        description TEXT  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        status_commission varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        price_Discount varchar(200)  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        id_media varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NULL,
        affiliatesstatus varchar(600)  NULL,
        affiliatespercentage varchar(600)  NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table affiliates".mysqli_error($connect);
        }
        $connect->query("INSERT INTO affiliates (description,id_media,status_commission,Discount,affiliatesstatus,affiliatespercentage) VALUES ('none','none','oncommission','onDiscountaffiliates','offaffiliates','0')");
    }
}
catch (Exception $e) {
    file_put_contents("$randomString.txt",$e->getMessage());
}
//----------------------- [ remove requests ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'cancel_service'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE cancel_service (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_user varchar(500)  NOT NULL,
        username varchar(1000)  NOT NULL,
        description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NOT NULL,
        status varchar(1000)  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table cancel_service".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log',$e->getMessage());
}
$connect->query("ALTER TABLE `user` CHANGE `Processing_value` `Processing_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");

//----------------------- [ Category ] --------------------- //
try {
    $result = $connect->query("SHOW TABLES LIKE 'category'");
    $table_exists = ($result->num_rows > 0);

    if (!$table_exists) {
        $result = $connect->query("CREATE TABLE category (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        remark varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin  NOT NULL)
        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin");
        if (!$result) {
            echo "table category".mysqli_error($connect);
        }
    }
} catch (Exception $e) {
    file_put_contents('error_log',$e->getMessage());
}
$connect->query("ALTER TABLE `user` CHANGE `Processing_value` `Processing_value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");