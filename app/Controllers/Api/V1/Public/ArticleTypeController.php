<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Public;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\ArticleTypeModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class ArticleTypeController extends BaseApiController
{
    protected ArticleTypeModel $articleTypeModel;

    public function __construct()
    {
        $this->articleTypeModel = new ArticleTypeModel();
    }

    /**
     * GET /public/article-types
     */
    public function index(): ResponseInterface
    {
        try {

            $records = $this->articleTypeModel
                ->active()
                ->ordered()
                ->select([
                    'id',
                    'uuid',
                    'title',
                    'code',
                    'slug',
                    'description',
                    'sort_order',
                ])
                ->findAll();

            return $this->successResponse(
                'Article types fetched successfully.',
                [
                    'items' => $records,
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch article types.'
            );
        }
    }

    /**
     * GET /public/article-types/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $articleType = $this->articleTypeModel
                ->active()
                ->select([
                    'id',
                    'uuid',
                    'title',
                    'code',
                    'slug',
                    'description',
                    'sort_order',
                ])
                ->where(
                    'uuid',
                    (string) $id
                )
                ->first();

            if (! $articleType) {
                return $this->notFoundResponse(
                    'Article type not found.'
                );
            }

            return $this->successResponse(
                'Article type fetched successfully.',
                $articleType
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch article type.'
            );
        }
    }
}