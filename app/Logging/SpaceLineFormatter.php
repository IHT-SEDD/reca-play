<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class SpaceLineFormatter extends LineFormatter
{
 public function format(array $record): string
 {
  return parent::format($record) . "\n";
 }
}
