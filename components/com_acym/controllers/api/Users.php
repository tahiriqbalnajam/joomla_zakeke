<?php

namespace AcyMailing\FrontControllers\Api;

use AcyMailing\Classes\FieldClass;
use AcyMailing\Classes\UserClass;

trait Users
{
    public function getUsers(): void
    {
        $options = [
            'offset' => acym_getVar('int', 'offset', 0),
            'limit' => acym_getVar('int', 'limit', 100),
            'filters' => acym_getVar('array', 'filters', []),
        ];

        $connector = acym_getVar('bool', 'connector', false);
        if ($connector) {
            $connectorName = empty($options['filters']['confirmed']) ? 'connector_trigger_getUsers' : 'connector_trigger_getConfirmedUsers';
            $lastTriggerDate = $this->config->get($connectorName);
            $this->config->save([$connectorName => date('Y-m-d H:i:s')]);

            if (empty($lastTriggerDate) || $lastTriggerDate < date('Y-m-d H:i:s', strtotime('-1 day'))) {
                $this->sendJsonResponse([]);
            }

            if (empty($options['filters']['confirmed'])) {
                $options['created_after'] = $lastTriggerDate;
            } else {
                $options['confirmed_after'] = $lastTriggerDate;
            }
        }

        $userClass = new UserClass();
        $users = $userClass->getXUsers($options);

        foreach ($users as $i => $oneUser) {
            $users[$i] = $this->removeExtraColumns(self::TYPE_USER, $oneUser);
        }

        $userIds = array_column($users, 'id');

        $fieldClass = new FieldClass();
        $userFields = $fieldClass->getAllFieldsByUserIds($userIds);
        foreach ($userFields as $userField) {
            $users[$userField->user_id]->{$userField->name} = $userField->value;
        }

        $this->sendJsonResponse(array_values($users));
    }

    public function deleteUser(): void
    {
        $email = acym_getVar('string', 'email', '');
        $userId = acym_getVar('int', 'userId', 0);

        $userClass = new UserClass();
        if (empty($email)) {
            if (empty($userId)) {
                $this->sendJsonResponse(['message' => 'Email or user ID not provided in query parameters.'], 422);
            } else {
                $user = $userClass->getOneById($userId);
            }
        } else {
            $user = $userClass->getOneByEmail($email);
        }

        if (empty($user)) {
            $this->sendJsonResponse(['message' => 'User not found.'], 404);
        }

        $affectedRows = $userClass->delete($user->id);

        if (empty($affectedRows)) {
            $this->sendJsonResponse(['message' => 'Error deleting user.', 'errors' => $userClass->errors], 500);
        }

        $this->sendJsonResponse(['message' => 'User deleted.']);
    }

    public function createOrUpdateUser(): void
    {
        $decodedData = acym_getJsonData();

        if (!isset($decodedData['email'])) {
            $this->sendJsonResponse(['message' => 'Email not provided in the request body.'], 422);
        }

        $userClass = new UserClass();
        $user = $userClass->getOneByEmail($decodedData['email']);

        if (empty($user)) {
            $user = new \stdClass();
            $user->email = $decodedData['email'];
        }

        if (isset($decodedData['name'])) {
            $user->name = $decodedData['name'];
        }

        if (isset($decodedData['active'])) {
            $user->active = $decodedData['active'];
        }

        if (isset($decodedData['confirmed'])) {
            $user->confirmed = $decodedData['confirmed'];
        }

        if (isset($decodedData['cmsId'])) {
            $user->cms_id = $decodedData['cmsId'];
        }

        if (isset($decodedData['sendConf'])) {
            $userClass->sendConf = $decodedData['sendConf'];
        }

        if (isset($decodedData['triggers'])) {
            $userClass->triggers = $decodedData['triggers'];
        }

        $customFields = $decodedData['customFields'] ?? [];

        $userId = $userClass->save($user, $customFields);

        if (empty($userId)) {
            $this->sendJsonResponse(['message' => 'Error saving user.', 'errors' => $userClass->errors], 500);
        }

        $this->sendJsonResponse(['userId' => $userId], 201);
    }
}
