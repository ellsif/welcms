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

    public static function getViewPath(string $viewPath): string
    {
        if (file_exists(welPocket()->getViewPath() . $viewPath)) {
            return welPocket()->getViewPath() . $viewPath;
        } elseif (file_exists(welPocket()->getSysPath() . 'views/' . $viewPath)) {
            return welPocket()->getSysPath() . 'views/' . $viewPath;
        }
        return '';
    }

    /**
     * パラメータの配列から連想配列を作ります。
     *
     * ## 説明
     * "/service/action/var1/10/var2/100"のようなリクエストから得られる
     * パラメータをハッシュにして返します。
     *
     * ## パラメータ
     *
     *     ['var1', '10', 'var2', '100', 'var2', '20', 'var3[]', '1', 'var3[]', '2', 'var4[foo]', 'foo', 'var4[bar]', 'bar']
     *
     * ## 返り値
     * 奇数番をキー、偶数番を値とした連想配列を返します。
     * $arrayのサイズが奇数の場合、最後の値はnullになります。
     * キーが重複する場合は後で指定された値で上書きされます。
     * ただし、キーの末尾が'[]'の場合、配列として、'[key]'の場合はハッシュとして追加されます。
     *
     *     [
     *       'var1' => '10',
     *       'var2' => '20',
     *       'var3' => ['1', '2'],
     *       'var4' => ['foo' => 'foo', 'bar' => 'bar']
     *     ]
     */
    public static function getParamMap($array)
    {
        $result = [];

        for($i = 0; $i < count($array); $i+=2) {
            $key = $array[$i];
            $val = $array[$i+1] ?? null;
            if ($val) $val = rawurldecode($val);
            $keys = [$key];

            // 配列の場合
            if (($startPos = mb_strpos($key, '[')) < ($endPos = mb_strrpos($key, ']'))) {
                $keys = [mb_substr($key, 0, $startPos)];
                $keyStr = mb_substr($key, $startPos + 1, $endPos - $startPos - 1);
                $subKeys = explode('][', $keyStr);
                $keys = array_merge($keys, $subKeys);
            }
            $target =& $result;
            for($j = 0; $j < count($keys) - 1; $j++) {
                $key = $keys[$j];
                if (!array_key_exists($key, $target)) $target[$key] = [];
                $target =& $target[$key];
            }
            if ($keys[count($keys)-1] === '') {
                $target[] = $val;
            } else {
                $target[$keys[count($keys)-1]] = $val;
            }
        }
        return $result;
    }
}