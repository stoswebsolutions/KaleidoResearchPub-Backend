<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Admin;

use App\Controllers\Api\V1\BaseApiController;
use App\Models\JournalModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class JournalController extends BaseApiController
{
    protected JournalModel $journalModel;

    protected array $allowedSortFields = [
        'title',
        'short_title',
        'impact_factor',
        'year_started',
        'status',
        'created_at',
    ];

    public function __construct()
    {
        $this->journalModel = new JournalModel();
    }

        /**
     * GET /journals
     */
    public function index(): ResponseInterface
    {
        try {

            $page = max(
                1,
                (int) (
                    $this->request->getGet('page')
                    ?? 1
                )
            );

            $perPage = min(
                100,
                max(
                    1,
                    (int) (
                        $this->request->getGet('per_page')
                        ?? 20
                    )
                )
            );

            $search = trim(
                (string) (
                    $this->request->getGet('search')
                    ?? ''
                )
            );

            $status = trim(
                (string) (
                    $this->request->getGet('status')
                    ?? ''
                )
            );

            $frequency = trim(
                (string) (
                    $this->request->getGet('frequency')
                    ?? ''
                )
            );

            $publicationType = trim(
                (string) (
                    $this->request->getGet('publication_type')
                    ?? ''
                )
            );

            $isIndexed = $this->request->getGet(
                'is_indexed'
            );

            $sortBy = (string) (
                $this->request->getGet('sort_by')
                ?? 'created_at'
            );

            $sortDirection = strtolower(
                (string) (
                    $this->request->getGet(
                        'sort_direction'
                    )
                    ?? 'desc'
                )
            );

            if (
                ! in_array(
                    $sortBy,
                    $this->allowedSortFields,
                    true
                )
            ) {
                $sortBy = 'created_at';
            }

            if (
                ! in_array(
                    $sortDirection,
                    ['asc', 'desc'],
                    true
                )
            ) {
                $sortDirection = 'desc';
            }

            $builder = $this->journalModel
                ->select([
                    'uuid',
                    'title',
                    'short_title',
                    'slug',
                    'thumbnail',
                    'issn_print',
                    'issn_online',
                    'impact_factor',
                    'frequency',
                    'publication_type',
                    'subject_area',
                    'is_indexed',
                    'year_started',
                    'status',
                    'created_at',
                ]);

            if ($search !== '') {

                $builder->groupStart()
                    ->like(
                        'title',
                        $search
                    )
                    ->orLike(
                        'short_title',
                        $search
                    )
                    ->orLike(
                        'slug',
                        $search
                    )
                    ->orLike(
                        'issn_print',
                        $search
                    )
                    ->orLike(
                        'issn_online',
                        $search
                    )
                    ->orLike(
                        'subject_area',
                        $search
                    )
                    ->groupEnd();
            }

            if ($status !== '') {

                $builder->where(
                    'status',
                    $status
                );
            }

            if ($frequency !== '') {

                $builder->where(
                    'frequency',
                    $frequency
                );
            }

            if ($publicationType !== '') {

                $builder->where(
                    'publication_type',
                    $publicationType
                );
            }

            if (
                $isIndexed !== null
                && $isIndexed !== ''
            ) {
                $builder->where(
                    'is_indexed',
                    (int) $isIndexed
                );
            }

            $records = $builder
                ->orderBy(
                    $sortBy,
                    $sortDirection
                )
                ->paginate($perPage);

            return $this->successResponse(
                'Journals fetched successfully.',
                [
                    'items' => $records,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => $this->journalModel
                            ->pager
                            ->getTotal(),
                        'last_page'    => $this->journalModel
                            ->pager
                            ->getPageCount(),
                    ],
                ]
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch journals.'
            );
        }
    }

        /**
     * GET /journals/{uuid}
     */
    public function show($id = null): ResponseInterface
    {
        try {

            $journal = $this->journalModel
                ->findByUuid(
                    (string) $id
                );

            if (! $journal) {
                return $this->notFoundResponse(
                    'Journal not found.'
                );
            }

            return $this->successResponse(
                'Journal fetched successfully.',
                $journal
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to fetch journal.'
            );
        }
    }

        /**
     * POST /journals
     */
    public function create(): ResponseInterface
    {
        try {

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? ''
                    )
                ),

                'short_title' => trim(
                    (string) (
                        $payload['short_title']
                        ?? ''
                    )
                ),

                'thumbnail' => trim(
                    (string) (
                        $payload['thumbnail']
                        ?? ''
                    )
                ),

                'description' => trim(
                    (string) (
                        $payload['description']
                        ?? ''
                    )
                ),

                'aims_scope' => trim(
                    (string) (
                        $payload['aims_scope']
                        ?? ''
                    )
                ),

                'issn_print' => trim(
                    (string) (
                        $payload['issn_print']
                        ?? ''
                    )
                ),

                'issn_online' => trim(
                    (string) (
                        $payload['issn_online']
                        ?? ''
                    )
                ),

                'doi_prefix' => trim(
                    (string) (
                        $payload['doi_prefix']
                        ?? ''
                    )
                ),

                'impact_factor' => (
                    $payload['impact_factor']
                    ?? null
                ),

                'frequency' => trim(
                    (string) (
                        $payload['frequency']
                        ?? ''
                    )
                ),

                'publication_type' => trim(
                    (string) (
                        $payload['publication_type']
                        ?? ''
                    )
                ),

                'subject_area' => trim(
                    (string) (
                        $payload['subject_area']
                        ?? ''
                    )
                ),

                'peer_review_type' => trim(
                    (string) (
                        $payload['peer_review_type']
                        ?? ''
                    )
                ),

                'is_indexed' => (int) (
                    $payload['is_indexed']
                    ?? 0
                ),

                'year_started' => (
                    ! empty($payload['year_started'])
                        ? (int) $payload['year_started']
                        : null
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
                        ?? ''
                    )
                ),

                'contact_email' => trim(
                    (string) (
                        $payload['contact_email']
                        ?? ''
                    )
                ),

                'contact_phone' => trim(
                    (string) (
                        $payload['contact_phone']
                        ?? ''
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? 'draft'
                    )
                ),

                'created_by' => $user['id'],
            ];

            if (
                ! $this->journalModel->insert(
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->journalModel->errors()
                );
            }

            $journal = $this->journalModel->find(
                $this->journalModel
                    ->getInsertID()
            );

            return $this->successResponse(
                'Journal created successfully.',
                $journal,
                ResponseInterface::HTTP_CREATED
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to create journal.'
            );
        }
    }

        /**
     * PUT /journals/{uuid}
     */
    public function update($id = null): ResponseInterface
    {
        try {

            $journal = $this->journalModel
                ->findByUuid(
                    (string) $id
                );

            if (! $journal) {
                return $this->notFoundResponse(
                    'Journal not found.'
                );
            }

            $payload = $this->request->getJSON(true);

            if (! is_array($payload)) {
                $payload = $this->request->getRawInput();
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $data = [
                'title' => trim(
                    (string) (
                        $payload['title']
                        ?? $journal['title']
                    )
                ),

                'short_title' => trim(
                    (string) (
                        $payload['short_title']
                        ?? $journal['short_title']
                    )
                ),

                'thumbnail' => trim(
                    (string) (
                        $payload['thumbnail']
                        ?? ($journal['thumbnail'] ?? '')
                    )
                ),

                'description' => (
                    $payload['description']
                    ?? $journal['description']
                ),

                'aims_scope' => (
                    $payload['aims_scope']
                    ?? $journal['aims_scope']
                ),

                'issn_print' => trim(
                    (string) (
                        $payload['issn_print']
                        ?? ($journal['issn_print'] ?? '')
                    )
                ),

                'issn_online' => trim(
                    (string) (
                        $payload['issn_online']
                        ?? ($journal['issn_online'] ?? '')
                    )
                ),

                'doi_prefix' => trim(
                    (string) (
                        $payload['doi_prefix']
                        ?? ($journal['doi_prefix'] ?? '')
                    )
                ),

                'impact_factor' => (
                    $payload['impact_factor']
                    ?? $journal['impact_factor']
                ),

                'frequency' => trim(
                    (string) (
                        $payload['frequency']
                        ?? ($journal['frequency'] ?? '')
                    )
                ),

                'publication_type' => trim(
                    (string) (
                        $payload['publication_type']
                        ?? ($journal['publication_type'] ?? '')
                    )
                ),

                'subject_area' => trim(
                    (string) (
                        $payload['subject_area']
                        ?? ($journal['subject_area'] ?? '')
                    )
                ),

                'peer_review_type' => trim(
                    (string) (
                        $payload['peer_review_type']
                        ?? ($journal['peer_review_type'] ?? '')
                    )
                ),

                'is_indexed' => (int) (
                    $payload['is_indexed']
                    ?? $journal['is_indexed']
                ),

                'year_started' => (
                    $payload['year_started']
                    ?? $journal['year_started']
                ),

                'website_url' => trim(
                    (string) (
                        $payload['website_url']
                        ?? ($journal['website_url'] ?? '')
                    )
                ),

                'contact_email' => trim(
                    (string) (
                        $payload['contact_email']
                        ?? ($journal['contact_email'] ?? '')
                    )
                ),

                'contact_phone' => trim(
                    (string) (
                        $payload['contact_phone']
                        ?? ($journal['contact_phone'] ?? '')
                    )
                ),

                'status' => trim(
                    (string) (
                        $payload['status']
                        ?? $journal['status']
                    )
                ),

                'updated_by' => $user['id'],
            ];

            if (
                ! $this->journalModel->update(
                    $journal['id'],
                    $data
                )
            ) {
                return $this->validationResponse(
                    $this->journalModel->errors()
                );
            }

            return $this->successResponse(
                'Journal updated successfully.',
                $this->journalModel->find(
                    $journal['id']
                )
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to update journal.'
            );
        }
    }

    /**
     * DELETE /journals/{uuid}
     */
    public function delete($id = null): ResponseInterface
    {
        try {

            $journal = $this->journalModel
                ->findByUuid(
                    (string) $id
                );

            if (! $journal) {
                return $this->notFoundResponse(
                    'Journal not found.'
                );
            }

            $authUser = service('authUser');

            $user = $authUser->profile;

            $this->journalModel->update(
                $journal['id'],
                [
                    'deleted_by' => $user['id'],
                ]
            );

            $this->journalModel->delete(
                $journal['id']
            );

            return $this->successResponse(
                'Journal deleted successfully.'
            );

        } catch (Throwable $e) {

            log_message(
                'error',
                $e->getMessage()
            );

            return $this->serverErrorResponse(
                'Unable to delete journal.'
            );
        }
    }
}