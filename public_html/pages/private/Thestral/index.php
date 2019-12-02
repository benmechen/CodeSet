<?php
/**
 * @Author: Ben
 * @Date: 2017-01-24 16:50:19
 * @Project: codeset.co.uk
 * @File Name: index.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-07-17 10:49:33
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../../config.php';

if (!$auth->isLogged()) {
    header("Location: ../../public/signin.php");
}

if ($_GET['id'] == -1) {
    header('Location: complete.php');
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

if ($_GET['id'] > $user['progress']+1 && $user['permission'] != 1) {
    $question = $user['progress']+1;
    header("Location: ../Thestral/index.php?id=$question");
}

$class = $codesetClasses->getClass($codesetClasses->getUsersClass($uid));

// if ($_GET['id'] > $user['progress']+1 && !isset($_GET['redirect'])) {
//     $question = $user['progress']+1;
//     header("Location: ../Thestral/index.php?id=$question&redirect=True");
// }

$question = $codesetThestral->getQuestion(htmlspecialchars($_GET['id']));

$allQuestions = $codesetThestral->getAllQuestions();

$savedCode = $codesetThestral->getSavedCode($uid, htmlspecialchars($_GET['id']));

if (!$question) {
    header("Location: ../Thestral/index.php?id=".$user['progress']);
}

if (isset($_POST['ask-teacher-submit'])) {
    $teacherEmail = $auth->getUser($class['author_id'])['email'];
    $email = $codesetClasses->composeStudentToTeacherEmail($teacherEmail, $user['name'], $user['email'], $class['name'], $question['id'], $_POST['ask-teacher-code'], $_POST['ask-teacher-instructions'], $_POST['ask-teacher-comments']);
    $codesetMail->Send("CodeSet <server@codeset.co.uk>", $email['email'], $email['subject'], $email['body']);
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>CodeSet - Learn Python</title>
        <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    </head>
    <body style="max-height: 100vh; max-width: 100%; overflow: hidden;">
        <header class="thestral-header">
            <nav>
                <a href="../" alt="Back"><img src="/public_html/assets/images/logo-black.png"></a>
                <ul>
                    <li>Learn Python</li>
                    <li style="margin-top: -4vh; margin-right: -70vw;"><span class="thestral-progress-circle <? if (intval($_GET['id']) >= intval($user['progress'])) {echo "thestral-progress-circle-uncomplete";} ?>"><?= $_GET['id'] ?></span></li>
                </ul>
            </nav>
        </header>
        <div class="popup-overlay" id="ask-teacher-overlay">
            <div class="popup" id="ask-teacher-popup">
                    <span class="popup-close" id="ask-teacher-close" style="cursor: pointer;">x</span>
                    <form method="post" action="">
                        <p style="color: white; font-size: 1.5em;">Your code will be emailed to your teacher. You can add any additional information in the box below.</p>
                        <textarea placeholder="Enter comments" cols="10" rows="4" name="ask-teacher-comments"></textarea>
                        <input type="hidden" name="ask-teacher-instructions" value="<?= htmlspecialchars($question['instructions']) ?>">
                        <input type="hidden" id="ask-teacher-code" name="ask-teacher-code" value="">
                        <br>
                        <input type="submit" name="ask-teacher-submit">
                    </form>
            </div>
        </div>
        <main>
            <div class="thestral-sidebar">
                <div class="thestral-sidebar-lessons" id="thestral-sidebar-lessons">
                    <div>
                        <?
                            $i = 1;
                            foreach ($allQuestions as $x) {
                                if ($_GET['id'] == $i) { ?>
                                    <a href="?id=<?= $x['id'] ?>" class="thestral-sidebar-lessons-active"><?= $i ?>. <?= $x['title'] ?></a>
                                <?} else { ?>
                                    <a href="?id=<?= $x['id'] ?>"><?= $i ?>. <?= $x['title'] ?></a>
                            <?    }
                                $i++;
                            } ?>
                    </div>
                    <a id="thestral-sidebar-arrow-in" onclick="closeNav()">Hide lessons &larr;</a>
                </div>
                <div class="thestral-sidebar-normal">
                    <br><br>
                    <h4><?= $question['title'] ?></h4>
                    <br>
                    <div class="thestral-sidebar-intro"><?= $question['intro'] ?></div>
                    <div class="thestral-sidebar-instructions">
                        <h5>INSTRUCTIONS</h5>
                        <br>
                        <?= $question['instructions'] ?>
                        <? if ($user['permission'] != 1) { ?>
                            <hr>
                            <a id="ask-teacher">Stuck? Ask your teacher</a>
                        <? } ?>
                    </div>
                    <input type="button" id="thestral-reset-code" value="Reset Code">
                    <a id="thestral-sidebar-arrow-out" onclick="openNav()">Show lessons &rarr;</a>
                </div>
            </div>
            <div class="thestral-editor" id="thestral-editor"><? if ($savedCode !== False) {
                echo $savedCode;
            } else {
                echo $question['default'];
            } ?></div>
            <div class="thestral-terminal" id="thestral-terminal"><span class="thestral-terminal-red">>></span></div>
            <div class="thestral-bar">
                <form class="thestral-bar-done" action="" method="get">
                    <? if ($_GET['id'] == $codesetThestral->getLastQuestionID()) { ?>
                        <input type="hidden" name="id" value="-1">
                    <? } else { ?>
                        <input type="hidden" name="id" value="<?= $_GET['id']+1 ?>">
                    <? } ?>
                    <input type="submit" name="" value="Done" id="thestral-bar-done" class="disabled" disabled>
                </form>
                <div class="thestral-bar-run">
                    <input type="button" id="thestral-bar-run" value="Run">
                </div>
            </div>
        </main>
    </body>
    <script>
        function openNav() {
            document.getElementById("thestral-sidebar-arrow-out").style.zIndex = "0";
            document.getElementById("thestral-sidebar-arrow-in").style.display = "inherit";
            document.getElementById("thestral-sidebar-lessons").style.width = "20%";
            document.getElementById("thestral-sidebar-lessons").style.padding = "1%";
        }

        /* Set the width of the side navigation to 0 and the left margin of the page content to 0 */
        function closeNav() {
            document.getElementById("thestral-sidebar-arrow-out").style.zIndex = "1";
            document.getElementById("thestral-sidebar-arrow-in").style.display = "none";
            document.getElementById("thestral-sidebar-lessons").style.width = "0";
            document.getElementById("thestral-sidebar-lessons").style.padding = "0";
        }
    </script>
    <script src="/public_html/libs/ace/ace.js" type="text/javascript" charset="utf-8"></script>
    <script>
        var editor = ace.edit("thestral-editor");
        editor.setTheme("ace/theme/tomorrow_night");
        editor.getSession().setMode("ace/mode/python");
        editor.getSession().setUseWrapMode(true);
        document.getElementById('thestral-editor').style.fontSize='0.9em';
        editor.focus(); //To focus the ace editor
        var n = editor.getSession().getValue().split("\n").length; // To count total no. of lines
        editor.gotoLine(n); //Go to end of document

        $(function(){
          $('#thestral-bar-run').on('click', function(e){
            $('#thestral-terminal').html('<p><span class="thestral-terminal-red">>>Running...</span></p>');

            var inputs = []
            var lines = editor.getValue().match(/[^\r\n]+/g);
            if (lines.length > 0) {
                for (var i = 0; i <= lines.length - 1; i++) {
                    if (lines[i].length > 0) {
                        if (lines[i].includes('input(')) {
                            if (lines[i].includes('#') == false || lines[i].indexOf('#') > lines[i].indexOf('input(')) {
                                for (var x = 0; x < lines[i].split("input(").length - 1; x++) {
                                    inputs.push(prompt("Enter the values for each of the inputs in your program:", " "));
                                }
                            }
                        }
                    }
                }
            }

            $("body").css({"cursor": "progress"});
            e.preventDefault();

            $.ajax({
              url: 'run.php',
              type: 'post',
              dataType: 'json',
              data: {'code': editor.getValue(), 'userID': <?= $uid ?>, 'questionID': <?= $_GET['id'] ?>, 'inputs': inputs},
              success: function(data) {
                console.log(data);
                $('#thestral-terminal').html('<p><span class="thestral-terminal-red">>></span>'+data[0]['message']+'</p>');
                if (data[1]['correct'] == true) {
                    $("#thestral-bar-done").removeAttr("class");
                    $("#thestral-bar-done").removeAttr("disabled");
                } else {
                    // console.log("nope");
                    $("#thestral-bar-done").attr("class", "disabled");
                    $("#thestral-bar-done").attr("disabled", true);
                }
                $("body").css({"cursor": "default"});
              },
              error: function(data) {
                $('#thestral-terminal').html('<p><span class="thestral-terminal-red">>></span>Error running code</p>');
                $("body").css({"cursor": "default"});
              }
            });
          });
        });
    </script>
    <script>
        $(function(){
          $('#thestral-reset-code').on('click', function(e){
            e.preventDefault();

            $.ajax({
              url: 'run.php',
              type: 'post',
              data: {'userID': <?= $uid ?>, 'questionID': <?= $_GET['id'] ?>},
              success: function(data) {
                console.log(data);
                if (data) {
                    location.reload();
                }
              },
              error: function(data) {
                alert("Could not reset code. Please try again.");
              }
            });
          });
        });

        document.getElementById('ask-teacher-code').value = editor.getValue();

        // Show ask teacher pop up
        $("#ask-teacher").click(function() {
            document.getElementById('ask-teacher-code').value = editor.getValue();
            $("#ask-teacher-overlay").fadeIn();
        });
        // Close ask teacher pop up
        $("#ask-teacher-close").click(function() {
            $("#ask-teacher-overlay").fadeOut();
        });
        $(document).keyup(function(e) {
            if (e.keyCode == 27) {
                $("#ask-teacher-overlay").fadeOut();
            }
        });
        $(document).mouseup(function (e) {
            var popup = $("#ask-teacher-popup");
            if (!$('#ask-teacher').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
                $("#ask-teacher-overlay").fadeOut();
            }
        });
    </script>
</html>
