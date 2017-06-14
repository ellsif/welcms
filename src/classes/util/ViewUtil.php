<?php
namespace ellsif\WelCMS;

class ViewUtil
{
    /**
     * エラーメッセージを表示します。
     */
    public static function printErrors($errors)
    {
        if ($errors) {
            $errorMessages = [];
            foreach($errors as $key => $messages) {
                if (is_array($messages)) {
                    $errorMessages[] = implode('<br>', $messages);
                }
            }
            if (count($errorMessages)) {
                echo '<div class="col-lg-12">';
                echo '<div class="alert alert-danger">';
                echo '<ul><li>' . implode('</li><li>', $errorMessages) . '</li></ul>';
                echo '</div></div>';
            }
        }
    }

    /**
     * HTMLエスケープした文字列を出力します。
     */
    public static function echo($str, $flags = ENT_COMPAT | ENT_HTML401)
    {
        echo htmlspecialchars($str, $flags);
    }
}