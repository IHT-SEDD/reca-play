<?php

namespace App\Services\Creator\AddData;

use Illuminate\Http\Request;

class NewDataFormRequestService
{
 /**
  * Mapping type ke Form Request class
  */
 protected array $formRequests = [
  'record' => \App\Http\Requests\Creator\StoreRecordRequest::class,
  'stream' => \App\Http\Requests\Creator\StoreStreamRequest::class,
 ];

 /**
  * Get instance of Form Request and validate the request
  */
 public function getValidatedData(string $type, Request $request): array
 {
  if (!isset($this->formRequests[$type])) {
   throw new \Exception("Form Request for {$type} not found");
  }

  $formRequestClass = $this->formRequests[$type];

  /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
  $formRequest = app($formRequestClass);

  // Merge request data and validate
  $formRequest->setContainer(app())
   ->setRedirector(app('redirect'))
   ->merge($request->all());

  return $formRequest->validated();
 }
}
