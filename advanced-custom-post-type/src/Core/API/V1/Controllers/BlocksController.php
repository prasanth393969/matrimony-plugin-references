<?php

namespace ACPT\Core\API\V1\Controllers;

use ACPT\Core\CQRS\Command\DeleteBlockCommand;
use ACPT\Core\CQRS\Command\SaveBlockCommand;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\JSON\DynamicBlockSchema;
use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Utils\Data\Sanitizer;

class BlocksController extends AbstractController
{
    /**
     * @param \WP_REST_Request $request
     * @return mixed
     */
    public function getAll(\WP_REST_Request $request)
    {
        try {
            $count = DynamicBlockRepository::count();
            $page = isset($request['page']) ? $request['page'] : 1;
            $perPage = isset($request['per_page']) ? $request['per_page'] : 20;
            $maxPages = ceil($count / $perPage);

            if($perPage > 100){
                $perPage = 100;
            }

            $records = DynamicBlockRepository::get([
                    'page' => $page,
                    'perPage' => $perPage,
            ]);

            return $this->jsonPaginatedResponse($page, $maxPages, $perPage, $count, $records);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $this->jsonErrorResponse($exception);
        }
    }

    /**
     * @param \WP_REST_Request $request
     * @return array|mixed
     */
    public function get(\WP_REST_Request $request)
    {
        $id = $request['id'];

        try {
            $meta = DynamicBlockRepository::get([
                'id' => $id,
            ]);

            if(null === $meta){
                return $this->jsonNotFoundResponse('Not records found');
            }

            return $this->jsonResponse($meta[0]);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $this->jsonErrorResponse($exception);
        }
    }

    /**
     * Create meta boxes
     *
     * @param \WP_REST_Request $request
     * @return mixed
     */
    public function create(\WP_REST_Request $request)
    {
        return $this->createOrUpdateDynamicBlock($request, 201);
    }

    /**
     * Save meta boxes
     *
     * @param \WP_REST_Request $request
     * @return mixed
     */
    public function update(\WP_REST_Request $request)
    {
        return $this->createOrUpdateDynamicBlock($request, 200, $request['id']);
    }

    /**
     * @param \WP_REST_Request $request
     * @param int $httpStatus
     * @param null $groupId
     *
     * @return mixed
     */
    private function createOrUpdateDynamicBlock(\WP_REST_Request $request, $httpStatus = 200, $groupId = null)
    {
        $data = $this->getDecodedRequest($request);

        if(empty($data)){
            return $this->jsonResponse([
                    'message' => 'empty request body'
            ], 500);
        }

        if(!is_array($data)){
            return $this->jsonResponse([
                    'message' => 'data is not an array'
            ], 500);
        }

        try {
            $id = $this->saveDynamicBlock($data, $groupId);

            return $this->jsonResponse([
                    'id' => $id
            ], $httpStatus);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $this->jsonErrorResponse($exception);
        }
    }

    /**
     * @param $data
     * @param null $groupId
     *
     * @return string
     * @throws \Exception
     */
    private function saveDynamicBlock($data, $groupId = null)
    {
        // validate data
        $this->validateJSONSchema($data, new DynamicBlockSchema());

        // sanitize data
        $data = Sanitizer::recursiveSanitizeRawData($data);

        $data['id'] = $groupId ? $groupId : Uuid::v4();
        $command = new SaveBlockCommand($data);

        return $command->execute();
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return mixed
     */
    public function delete(\WP_REST_Request $request)
    {
        $id = $request['id'];

        try {
            $command = new DeleteBlockCommand($id);
            $command->execute();

            return $this->jsonResponse([
                    'id' => $id
            ], 200);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $this->jsonErrorResponse($exception);
        }
    }
}