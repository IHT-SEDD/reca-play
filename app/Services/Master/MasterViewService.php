<?php

namespace App\Services\Master;

use Illuminate\Support\Facades\File;

class MasterViewService
{
 protected array $availableViews = [];

 public function __construct()
 {
  $this->availableViews = $this->discoverViews();
 }

 /**
  * Automatically discover master views
  * Scans: resources/views/pages/master/*
  */
 protected function discoverViews(): array
 {
  $views = [];
  $basePath = resource_path('views/pages/master');

  if (! File::exists($basePath)) {
   return $views;
  }

  foreach (File::directories($basePath) as $dir) {
   $folder = basename($dir);
   $indexFile = $dir . '/index.blade.php';

   // hanya daftarkan kalau ada index.blade.php
   if (File::exists($indexFile)) {
    $views[$folder] = "pages.master.{$folder}.index";
   }
  }

  return $views;
 }

 // Get view based on $type
 public function getView(string $type): ?string
 {
  return $this->availableViews[$type] ?? null;
 }

 // Get all view
 public function all(): array
 {
  return array_keys($this->availableViews);
 }

 // Check the view is accessible or not
 public function exists(string $type): bool
 {
  return array_key_exists($type, $this->availableViews);
 }
}
