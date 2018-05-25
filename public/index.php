<?php
/**
 * File index.php
 *
 * PHP version 5.6
 *
 * @category Public
 * @package  FastApi
 * @author   mmfjunior1@gmail.com <mmfjunior1@gmail.com>
 * @license  mmfjunior1@gmail.com Proprietary
 * @link     
 */
require '../vendor/autoload.php';

$validateLicense = (function ($licState, $newCookie) {
    $SUCESS_LICENSE_OK = 'LICENSE_OK';
    
    $publicPaths = explode("@", "/aklicd/aklicense.*");

    $isPublic = array_filter(
        array_map(
            function ($path) {
                return preg_match('@'.$path.'@', getenv('REQUEST_URI'));
            }, $publicPaths
        )
    );
    setcookie('LICSTATUS', $licState, 0, '/', null, true);
    if ($SUCESS_LICENSE_OK == $licState) {
        return setcookie('DOMAINID', $newCookie, 0, '/', null, true);
    }

    setcookie('DOMAINID', '', 0, '/', null, true);

    /*if (empty($isPublic)) {
        ob_clean();
        header('Content-Type: application/json');
        die(json_encode(array("msg" => "Invalid License: $licState", "status" => false)));
    }*/

});
/**
 * Index start
 * em variavel para nao permitir criar antes
 */
$checkLicenseStatus = (function ($callback) {
    // nao pode estar fora, pois poderar mudar
    $SUCESS_LICENSE_OK = 'LICENSE_OK';
    $ERROR_INVALID_BINARY = 'INVALID_BINARY';
    $ERROR_LICENSE_NOTFOUND = 'LICENSE_NOTFOUND';
    $NONCE = "fbb0977ad2153be1c53fbfe16c02d625de5fad2a";
    $PUBLIC_KEY = <<<EOF
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwFl1XFpaZcRLDOWTIFKV
YyLBmdDIbtnsO5torFj3/7etCh2rh+xw488C3wDpCXrBayaREk7rTT5LdaJBfO8/
vxmN7NLqq8Te52LN/3K9lOxNrMSJW1CXpDCxj56cgaEVbRFmwxgRULmEUyDuU6Lw
KV/KhxMkA057G/qtsFoMEPgro7e1bZZrWvNzOzDw42aKKU/nAa1oe37L0S2S3rk6
iziWmzR3deyo8ERaZkj2SBldepVzYL9AMmJ6neQQbmixaN4TJD4SFXOzuxxRnysi
Yal6SnYZ27IzeN8yPS6fOHaBCu+HokZ64b67KxrYHLLNGpGaqUPp1ruS4ktAMC4m
fv7qTFEzQI4zF6IuLhpOxw7Akkhsj3E3duGxS+KnrHX3Ku+h6GCWGOuSh3SKX1Tx
mbF7F1EMP4XOAN/hgKB7gFc610dE+q/NNEbh1H1OTf9oypbct/Ao3bISWX0RB2+1
RiHRtRlBd3E00/bcyrZoQR3LksBPryrgTTiOi3TyuK2L88Lf/LAyuCB42ylePOr7
fIKj1NTm3hL7Xzh5jNW5D0OcISqHcDr7juVj48Jtn8w4tNDGXVROUwJpSmC64EU1
qkFyJ8Hvie7OA7xgIV8KjM/O6r2zWLVhJq2autOQG5cjOxRFzzWJLiFvCEYC5xB3
sV1SM7FvembTkx7KOjUFmCMCAwEAAQ==
-----END PUBLIC KEY-----

EOF;

    $currentCookie = filter_input(INPUT_COOKIE, 'DOMAINID', FILTER_SANITIZE_STRING);
    $maxRandValue = "10";// 0 -- max == 6, valida cookie
    $callValidateDate = $NONCE . date('YmdG');

    $invalidCookie = false;
    $newCookie = "";
    if (rand(0, $maxRandValue) == 6 || empty($currentCookie)) {
        $newCookie = hash('sha1', $NONCE . $callValidateDate);
        $invalidCookie = ($newCookie != $currentCookie);
    }

    if ($invalidCookie) {

        $binarySignature = @file_get_contents('/aker/aklicense/aklicd.sign');
        $binaryCheckSum = @md5_file('/aker/aklicense/aklicd');
        $binaryCheckSumSign = "";
        openssl_public_decrypt($binarySignature, $binaryCheckSumSign, $PUBLIC_KEY);
        $result = $ERROR_LICENSE_NOTFOUND;

        if (empty($binaryCheckSum) || $binaryCheckSumSign != $binaryCheckSum) {
            return $callback($ERROR_INVALID_BINARY, null);
        }

        $result = trim(shell_exec('sudo /aker/aklicense/aklicd' . " --lic-state"));

        if ($SUCESS_LICENSE_OK != $result) {
            return $callback($result, null);
        }

        return $callback($SUCESS_LICENSE_OK, $newCookie);
    }

});
if ("cli" != php_sapi_name()) {
    $checkLicenseStatus($validateLicense); // sem teste e validando o cookie 1micro  | executando e validando 315 micro
}
$app = new App\Bootstrap\Init();
$app->run();