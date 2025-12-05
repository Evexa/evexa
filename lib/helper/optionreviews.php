<?
namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class OptionReviews
{
    public static function getSites(): array
    {
        $resultEntity = \Bitrix\Main\SiteTable::getList([
            'select' => ['NAME', 'LID'],
            'filter' => ['ACTIVE' => 'Y'],
        ])->fetchAll();

        return array_column($resultEntity, 'NAME', 'LID') ?? [];
    }

    public static function setDefault(array $sites2Set = []): void
    {
        $sites2Set = array_fill_keys($sites2Set, '');

        $sites = $sites2Set ?: self::getSites();
        $default = static::getDefaultConfigs();

        foreach ($sites as $siteID => $site) {
            foreach ($default as $name => $value) {
                Option::set(
                    \CSotbitReviews::iModuleID,
                    $name,
                    $value,
                    $siteID,
                );
            }
        }
    }

    public static function getDefaultConfigs(): array
    {
        $defaultOptionsFile = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sotbit.reviews/default_option.php';

        if (!file_exists($defaultOptionsFile)) {
            return [];
        }

        include $defaultOptionsFile;

        if (isset($sotbit_reviews_default_option) && is_array($sotbit_reviews_default_option)) {
            return $sotbit_reviews_default_option;
        }

        return [];
    }

    public function getReviewsDefaultConfigs()
    {

    }

    public static function getConfigs(?string $site = null): array
    {
        $siteConfig = Option::getForModule(\CSotbitReviews::iModuleID, $site);

        foreach ($siteConfig as $key => $value) {
            if (self::is_serialized_string($value)) {
                $siteConfig[$key] = unserialize($value);
            }
        }

        return $siteConfig;
    }

    public static function getConfigsReviews(?string $site = null): array
    {
        $siteConfig = Option::getForModule(\CSotbitReviews::iModuleID, $site);

        foreach ($siteConfig as $key => $value) {
            if (!str_contains($key, 'REVIEWS')) {
                continue;
            }

            $result[$key] = $value;
            if (self::is_serialized_string($value)) {
                $result[$key] = unserialize($value);
            }

        }

        if (empty($result)) {
            $result = Option::getDefaults('sotbit.reviews');
        }

        return $result;
    }

    public static function getConfigsQuestions(?string $site = null): array
    {
        $siteConfig = Option::getForModule(\CSotbitReviews::iModuleID, $site);

        foreach ($siteConfig as $key => $value) {
            if (!str_contains($key, 'QUESTIONS')) {
                continue;
            }

            $result[$key] = (self::is_serialized_string($value))
                ? unserialize($value)
                : $value;
        }

        if (empty($result)) {
            $result = Option::getDefaults('sotbit.reviews');
        }

        return $result;
    }

    public static function deleteConfigs(): void
    {
        Option::delete(\CSotbitReviews::iModuleID);
    }

    public static function getConfig($name, ?string $site = null): mixed
    {
        $config = Option::get(\CSotbitReviews::iModuleID, $name, '', $site);

        if (self::is_serialized_string($config)) {
            $config = unserialize($config);
        }

        return $config;
    }

    public static function setSiteParam(string $name, mixed $value, string $site): mixed
    {
        if (is_array($value)) {
            $value = serialize($value);
        }

        Option::set(
            \CSotbitReviews::iModuleID,
            $name,
            $value === 0 ? 0 : $value,//?: 'N'
            $site
        );

        if (self::is_serialized_string($value)) {
            $value = unserialize($value);
        }

        return $value;
    }

    public static function is_serialized_string($data, $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (!str_contains($data, '"')) {
                    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
            case 'E':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
    }

    public static function getConfigImageReview($site): array
    {
        $result = [];
        $arOption = [
            "REVIEWS_UPLOAD_FILE",
            "REVIEWS_MAX_IMAGE_SIZE",
            "REVIEWS_MAX_COUNT_IMAGES",
            "REVIEWS_MAX_VIDEO_SIZE",
            "REVIEWS_MAX_COUNT_VIDEO",
        ];

        foreach ($arOption as $option) {
            $result[$option] = Option::get(\CSotbitReviews::iModuleID, $option, '', $site);
        }

        return $result;
    }
}

?>
