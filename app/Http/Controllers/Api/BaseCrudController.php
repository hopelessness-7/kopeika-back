<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\BaseCrudService;
use App\Http\Requests\BaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseCrudController extends BaseApiController
{
    abstract protected function crudService(): BaseCrudService;

    abstract protected function resourceClass(): string;

    public function index(): JsonResponse
    {
        $resourceClass = $this->resourceClass();

        return $this->success(
            $resourceClass::collection($this->crudService()->index($this->resolveUserId())),
        );
    }

    public function show(int $id): JsonResponse
    {
        $resourceClass = $this->resourceClass();

        return $this->success(
            $resourceClass::make($this->crudService()->show($id, $this->resolveUserId())),
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->crudService()->destroy($id, $this->resolveUserId());

        return $this->noContent();
    }

    protected function respondStored(BaseRequest $request, callable $store): JsonResponse
    {
        $resourceClass = $this->resourceClass();
        $model = $store($request->getDto());

        return $this->created($resourceClass::make($model));
    }

    protected function respondUpdated(int $id, BaseRequest $request, callable $update): JsonResponse
    {
        $resourceClass = $this->resourceClass();
        $model = $update($id, $request->getDto());

        return $this->success($resourceClass::make($model));
    }
}
