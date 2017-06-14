<?php
namespace ellsif\WelCMS;

class ViewUtil
{
    /**
     * Flashメッセージを登録します。
     */
    public static function addFlash($message, $level = 'info')
    {
        Pocket::getInstance()->addFlash($message, $level);
    }

    /**
     * Flashメッセージを表示します。
     */
    public static function printFlash()
    {
        $flash = Pocket::getInstance()->varFlash();
        if ($flash && is_array($flash) && count($flash)) {
            foreach($flash as $level => $messages) {
                echo '<div class="col-lg-12"><div class="alert alert-' . ViewUtil::htmlEscape($level) . '"><ul>';
                foreach($messages as $message) {
                    echo '<li>';
                    ViewUtil::echo($message);
                    echo '</li>';
                }
                echo '</ul></div></div>';
            }
        }
    }

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
    public static function echo($str, $flags = null)
    {
        echo ViewUtil::htmlEscape($str, $flags);
    }

    /**
     * HTMLエスケープします。
     */
    public static function htmlEscape($str, $flags = null)
    {
        if ($flags == null) {
            $flags = ENT_COMPAT | ENT_HTML401;
        }
        return htmlspecialchars($str, $flags);
    }
}