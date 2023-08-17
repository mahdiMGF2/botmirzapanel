<?php
function latestRelease(){
    $url = "https://api.github.com/repos/mahdigholipour3/bottelegrammarzban/releases/latest";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
$response = curl_exec($curl);
curl_close($curl);
$latestRelease = json_decode($response, true);
$tagName = "";
if ($latestRelease) {
    $tagName = $latestRelease['tag_name'];
}
return $tagName;

}
$latestRelease = latestRelease();
$version = file_get_contents('../version');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آپدیت ربات</title>
    <style>
        @font-face {
            font-family: 'vazir';
            src: url('/Vazir.eot');
            src: local('☺'), url('../fonts/Vazir.woff') format('woff'), url('../fonts/Vazir.ttf') format('truetype');
        }

        input,
        button,
        body {
            font-family: vazir;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            height: 100vh;
            background: linear-gradient(150deg, #00188f, #00011d);
        }

        .boxversion {
            display: flex;
            height: 100vh;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 0;

        }

        .boxversion div {
            background-color: rgb(148, 122, 255);
            border-radius: 20px;
            display: flex;
            flex-direction: row;
            width: 40%;

        }

        .boxversion section {
            text-align: center;
            line-height: 40px;
            color: #fff;
            padding: 20px;
            width: 50%;
        }

        .btn {
            padding: 20px 50px;
            background-color: rgb(237, 41, 41);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            display: block;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="boxversion">
        <div>
            <section style="border-right: 2px solid #fff">
                <h2>نسخه فعلی</h2>
                <h2><?php echo $version ?></h2>
            </section>
            <section>
                <h2>نسخه آخر منتشر شده</h2>
                <h2><?php echo $latestRelease ?></h2>
            </section>
        </div>
        <?php
        if($latestRelease > $version){
            ?>
        <a class="btn" href="../update.php">آپدیت ربات</a>
        <?Php
        }
        else{
        ?>
        <?php
        }
        ?>
    </div>
</body>

</html>