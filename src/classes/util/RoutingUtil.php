<?php


namespace ellsif\WelCMS;


use ellsif\util\StringUtil;

class RoutingUtil
{
    /**
     * URLをパースする。
     *
     * ## 説明
     * parse_url()のパース結果に幾つか項目を追加して返します。
     *
     * ## パラメータ
     *
     * ## 返り値
     * 正しいURLでない場合は空配列を返します。
     * パースに成功した場合、連想配列を返します。連想配列には以下の要素が含まれる可能性があります。
     *
     * - scheme
     * - host
     * - port
     * - user
     * - pass
     * - path
     * - query ("?"以降)
     * - fragment ("#"以降)
     * - paths (pathを"/"で分割した配列)
     * - params (queryを分割した連想配列)
     *
     * ## 変更履歴
     * - 初回実装
     *
     * ## 例
     *
     */
    public static function parseUrl(string $url) :array
    {
        if (!WelUtil::isUrl($url)) {
            if (isset($_SERVER['HTTPS'])) {
                $url = 'https://' . self::getHostname() . $url;
            } else {
                $url = 'http://' . self::getHostname() . $url;
            }
        }
        $urlInfo = parse_url($url);
        if ($urlInfo === false) {
            throw new \InvalidArgumentException($url . ' is invalid URL', 500);
        }

        if (welPocket()->getInstallDirectory()) {
            // index.phpがドキュメントルート以下に無い場合は置き換え
            $urlInfo['path'] = StringUtil::leftRemove($urlInfo['path'], '/' . welPocket()->getInstallDirectory());
        }
        $path = $urlInfo['path'];
        $paths = array_filter(explode('/', $path), "strlen");
        $urlInfo['paths'] = array_values($paths);

        $urlInfo['params'] = [];
        if (isset($urlInfo['query'])) {
            $urlInfo['params'] = self::parseQuery($urlInfo['query']);
        }
        return $urlInfo;
    }

    // queryをパースする
    public static function parseQuery(string $query) :array
    {
        $results = [];
        $params = explode('&', $query);
        foreach ($params as $param) {
            list($name, $val) = explode('=', $param);
            if (strpos($name, '[]') > 0) {
                $name = substr($name, 0, strrpos($name, "[]"));
                if (!isset($results[$name])) {
                    $results[$name] = [];
                }
                $results[$name][] = $val;
            } else {
                $results[$name] = $val;
            }
        }
        return $results;
    }

    // ホスト名を取得する
    public static function getHostname() :string
    {
        if ($_SERVER['HTTP_HOST']) {
            return $_SERVER['HTTP_HOST'];
        } else {
            throw new Exception('failed to get http hostname', 0, null);
        }
    }
}