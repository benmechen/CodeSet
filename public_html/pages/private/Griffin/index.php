<?php
/**
 * @Author: Ben
 * @Date: 2017-01-24 16:50:19
 * @Project: codeset.co.uk
 * @File Name: index.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-04-25 22:15:18
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../../config.php';

if (!$auth->isLogged()) {
    header("Location: ../../public/signin.php");
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

$savedCode = $codesetThestral->getSavedCode($uid, "INT");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>CodeSet - Python Editor</title>
        <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    </head>
    <body style="max-height: 100vh; max-width: 100%; overflow: hidden;">
        <header class="thestral-header">
            <nav>
                <a href="../" alt="Back"><img src="/public_html/assets/images/logo-black.png"></a>
                <ul>
                    <li>Python Editor</li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="griffin-editor" id="griffin-editor"><? if ($savedCode !== False) {
                echo $savedCode;
            } else {
                echo $question['default'];
            } ?></div>
            <div class="griffin-terminal" id="griffin-terminal"><span class="thestral-terminal-red">>></span></div>
            <div class="thestral-bar">
                <div class="thestral-bar-run">
                    <input type="button" id="thestral-bar-run" value="Run">
                </div>
            </div>
        </main>
    </body>
    <script src="/public_html/libs/ace/ace.js" type="text/javascript" charset="utf-8"></script>
    <script>
        function openNav() {
            document.getElementById("thestral-sidebar-arrow-out").style.zIndex = "0";
            document.getElementById("thestral-sidebar-lessons").style.width = "20%";
            document.getElementById("thestral-sidebar-lessons").style.padding = "1%";
        }

        /* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
        function closeNav() {
            document.getElementById("thestral-sidebar-arrow-out").style.zIndex = "1";
            document.getElementById("thestral-sidebar-lessons").style.width = "0";
            document.getElementById("thestral-sidebar-lessons").style.padding = "0";
        }

        var editor = ace.edit("griffin-editor");
        editor.setTheme("ace/theme/tomorrow_night");
        editor.getSession().setMode("ace/mode/python");
        editor.getSession().setUseWrapMode(true);
        document.getElementById('griffin-editor').style.fontSize='0.9em';
        editor.focus(); //To focus the ace editor
        var n = editor.getSession().getValue().split("\n").length; // To count total no. of lines
        editor.gotoLine(n); //Go to end of document
    </script>
    <script>
        $(function(){
          $('#thestral-bar-run').on('click', function(e){
            $('#griffin-terminal').html('<p><span class="thestral-terminal-red">>>Running...</span></p>');
            var inputs = []
            var lines = editor.getValue().match(/[^\r\n]+/g);
            if (lines.length > 0) {
                for (var i = 0; i <= lines.length - 1; i++) {
                    if (lines[i].length > 0) {
                        if (lines[i].includes('#') == false) {
                            for (var x = 0; x < lines[i].split("input(").length - 1; x++) {
                                inputs.push(prompt("Enter the values for each of the inputs in your program:", " "));
                            }
                        }
                    }
                }
            }
            
            $("body").css({"cursor": "progress"});
            e.preventDefault();

            $.ajax({
              url: '../Thestral/run.php',
              type: 'post',
              dataType: 'json',
              data: {'code': editor.getValue(), 'userID': <?= $uid ?>, 'questionID': 'INT', 'inputs': inputs},
              success: function(data) {
                console.log(data);
                $('#griffin-terminal').html('<p><span class="thestral-terminal-red">>></span>'+data[0]['message']+'</p>');
                if (data[1]['correct'] == true) {
                    $("#thestral-bar-done").removeAttr("class");
                    $("#thestral-bar-done").removeAttr("disabled");
                } else {
                    $("#thestral-bar-done").attr("class", "disabled");
                    $("#thestral-bar-done").attr("disabled", true);
                }
                $("body").css({"cursor": "default"});
              },
              error: function(data) {
                console.log(data);
                $('#griffin-terminal').html('<p><span class="thestral-terminal-red">>></span>Error running code</p>');
                $("body").css({"cursor": "default"});
              }
            });
          });
        });
    </script>
</html>