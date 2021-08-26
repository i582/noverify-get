<?php

namespace NoVerify;

use Exception;
use ZipArchive;

class Downloader {
  private const BASE_PATH = 'https://github.com/VKCOM/noverify/releases/download/';

  public const VERSIONS = [
    "0.3.0",
    "0.4.0",
  ];

  /**
   * @throws Exception
   */
  private static function osName(): string {
    $name = php_uname('s');
    $name = strtolower($name);

    if (strpos($name, "windows") !== false) {
      return "windows";
    } elseif (strpos($name, "darwin") !== false) {
      return "darwin";
    } elseif (strpos($name, "linux") !== false) {
      return "linux";
    }

    throw new Exception("Not supported os: " . $name);
  }

  /**
   * @throws Exception
   */
  private static function osArch(): string {
    $name = php_uname('m');

    if (strpos($name, "x86_64") !== false) {
      return "amd64";
    } elseif (strpos($name, "arm64") !== false) {
      return "arm64";
    }

    throw new Exception("Not supported arch: " . $name);
  }

  /**
   * @throws Exception
   */
  public static function process(string $bin_folder, string $version) {
    if ($version === "latest") {
      $version = self::VERSIONS[count(self::VERSIONS) - 1];
    }

    echo "Start download v$version version...\n";
    self::download($bin_folder, $version);
    echo "Successful download v$version version\n";
    echo "Start extract v$version version...\n";
    self::extract($bin_folder, $version);
    echo "Successful extracted v$version version\n";
  }

  /**
   * @throws Exception
   */
  public static function download(string $bin_folder, string $version) {
    if ($version === "0.2.0" || $version === "0.1.0") {
      throw new Exception("Version v$version cannot be downloaded");
    }

    $os   = self::osName();
    $arch = self::osArch();

    $abs_path = self::BASE_PATH . "/v$version/noverify-$os-$arch.zip";

    $contents = @file_get_contents($abs_path);

    if ($contents === false && $arch === "arm64") {
      echo "Arm64 version not found\n";
      echo "Try download amd64 version\n";
      // Try download amd64 version.
      $abs_path = self::BASE_PATH . "/v$version/noverify-$os-amd64.zip";
      $contents = @file_get_contents($abs_path);
      if ($contents === false) {
        throw new Exception("Version v$version not found, available versions: " .
          join(", ", self::VERSIONS));
      }
      echo "Successful found amd64 version\n";
    } elseif ($contents === false) {
      throw new Exception("Version v$version not found, available versions: " .
        join(", ", self::VERSIONS));
    }

    @mkdir("vendor/bin");
    file_put_contents("./vendor/bin/noverify-$version.zip", $contents);
  }

  /**
   * @throws Exception
   */
  public static function extract(string $bin_folder, string $version): bool {
    $zip = new ZipArchive;

    $archive_name = "./vendor/bin/noverify-$version.zip";
    $res          = $zip->open($archive_name);
    if ($res === false) {
      throw new Exception("Archive $archive_name not opened");
    }

    $zip->extractTo("./vendor/bin");
    system("chmod +x ./vendor/bin/noverify");
    return true;
  }
}
