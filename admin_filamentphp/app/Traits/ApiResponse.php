<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponse
{
    /**
     * Success response.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $message = null, $code = 200, array $extraMeta = [])
    {
        $message = $message ?? __('admin.api.success');

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $extraMeta
        ];

        // Handle Pagination Metadata for API Resources
        if ($data instanceof ResourceCollection) {
            $dataArray = $data->toResponse(request())->getData(true);
            $response['data'] = $dataArray['data'] ?? [];
            
            $response['meta'] = array_merge($response['meta'], $dataArray['meta'] ?? []);
            
            if (isset($dataArray['links'])) {
                $response['links'] = $dataArray['links'];
            }
        } elseif ($data instanceof JsonResource) {
            $response['data'] = $data->resolve();
        }

        return response()->json($response, $code);
    }

    /**
     * Error response.
     *
     * @param string|null $message
     * @param int $code
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message = null, $code = 400, $data = null)
    {
        $message = $message ?? __('admin.api.error');

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function ok($data = null, $message = null)
    {
        return $this->success($data, $message, 200);
    }

    protected function created($data = null, $message = null)
    {
        $message = $message ?? __('admin.api.created');
        return $this->success($data, $message, 201);
    }

    protected function noContent()
    {
        return response()->json(null, 204);
    }

    protected function badRequest($message = null)
    {
        $message = $message ?? __('admin.api.bad_request');
        return $this->error($message, 400);
    }

    protected function unauthorized($message = null)
    {
        $message = $message ?? __('admin.api.unauthorized');
        return $this->error($message, 401);
    }

    protected function forbidden($message = null)
    {
        $message = $message ?? __('admin.api.forbidden');
        return $this->error($message, 403);
    }

    protected function notFound($message = null)
    {
        $message = $message ?? __('admin.api.not_found');
        return $this->error($message, 404);
    }

    protected function validationError($errors, $message = null)
    {
        $message = $message ?? __('admin.api.validation_error');
        return $this->error($message, 422, $errors);
    }
}
