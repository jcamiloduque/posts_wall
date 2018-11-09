<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sqlite_exists = false;

if(class_exists("SQLite3")){
    $sqlite_exists = true;
    $db = new SQLite3('db/posts.db');
    if(isset($_POST)&&isset($_POST["url"])){
        include_once("includes/HTMLConvert.php");
        echo json_encode(HTMLConvert::getPageMetadata($_POST["url"]));
        exit;
    }

    if(isset($_POST)&&isset($_POST["action"])&&isset($_POST["txt"])&&$_POST["action"]=="post"){
        if(!isset($_POST["obj"]))$_POST["obj"] = "";
        $db->query("insert into posts (date, content, object) values ('".date("Y-m-d H:i:s")."', '".$db::escapeString((string)$_POST["txt"])."', '".$db::escapeString((string)$_POST["obj"])."')");
        echo "true";
        exit;
    }
    include_once("includes/DateOptions.php");
    $results = $db->query('select * from posts order by date desc');
}
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name = "viewport" content = "user-scalable=no, width=device-width">
        <title>Test</title>
        <script type="text/javascript" src="scripts/frw.min.js"></script>
        <script type="text/javascript" src="scripts/script.js"></script>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
    </head>
    <body class="_small">
        <?php if(!$sqlite_exists){ ?>
        <div class="no_support">
            <div class="container">
                <div class="message">
                    <div class="window">
                        <div class="title">Missing</div>
                        <div class="content">SQLite3 component is required</div>
                    </div>
                </div>
            </div>
        </div>
        <?php } else if(!function_exists("curl_init")){ ?>
        <div class="no_support">
            <div class="container">
                <div class="message">
                    <div class="window">
                        <div class="title">Missing</div>
                        <div class="content">The CURL component is required</div>
                    </div>
                </div>
            </div>
        </div>
        <?php } else if(!function_exists("mb_strlen")){ ?>
        <div class="no_support">
            <div class="container">
                <div class="message">
                    <div class="window">
                        <div class="title">Missing</div>
                        <div class="content">The mbstring component is required</div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="_border"></div>
        <div id="container">
            <div class="_border"></div>
            <div class="wrapper_container">
                <div class="wrapper">
                    <div id="textContainer"></div>
                    <div id="masonry" class="masonry">
                        <div class="col write">
                            <div id="_text"></div>
                            <div class="textarea_pick"></div>
                            <div class="optns">
                                <ul>
                                    <li class="write">
                                        <i class="fa fa-pencil" aria-hidden="true"></i><br>Text
                                    </li>
                                </ul>
                            </div>
                            <div id="object"></div>
                            <div class="buttons"><button disabled id="share" ><i style="display: none;" class="fa fa-spinner fa-pulse"></i>Share</button><input id="cancel" value="Cancel" type="button"></div>
                        </div>
                        <?php if($sqlite_exists) while ($row = $results->fetchArray()) { ?>
                            <div class="col">
                                <div class="tbl">
                                    <div style="width: 0;">
                                        <span class="fa-stack fa-lg">
                                          <i class="fa fa-circle fa-stack-2x"></i>
                                          <i class="fa fa-user fa-stack-1x fa-inverse"></i>
                                        </span>
                                    </div>
                                    <div class="hdr">
                                        <div class="_name">Guest</div>
                                        <div class="_date"><?php echo DateOptions::str_diff(DateTime::createFromFormat("Y-m-d H:i:s", $row["date"])); ?></div>
                                    </div>
                                </div>
                                <div class="_cnt_"><?php echo str_replace("\n", "<br>", htmlentities(!is_string($row["content"])?"":$row["content"])); ?><?php echo $row["object"]; ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer">
            <div class="left">
                &copy; <?=date("Y")?> Juan Camilo Duque Restrepo
            </div>
            <div class="right">
                Version 1.0
            </div>
        </div>
    </body>
</html>
