<?php

namespace App\Services\Master;

class MasterViewService
{
 // Available master view is accessible
 protected array $availableViews = [
  'field' => 'pages.master.field.index',
  'role' => 'pages.master.role.index',
  'category' => 'pages.master.category.index',
  'venue' => 'pages.master.venue.index',
  'venue-type' => 'pages.master.venue-type.index',
  'camera' => 'pages.master.camera.index',
  'nvr' => 'pages.master.nvr.index',
  'qr_code' => 'pages.master.qr_code.index',
 ];

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
