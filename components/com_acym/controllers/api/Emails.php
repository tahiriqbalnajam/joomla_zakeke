<?php

namespace AcyMailing\FrontControllers\Api;

use AcyMailing\Classes\MailClass;
use AcyMailing\Classes\UserClass;
use AcyMailing\Helpers\MailerHelper;
use AcyMailing\Classes\QueueClass;

trait Emails
{
    public function insertEmailInQueue(): void
    {
        $decodedData = acym_getJsonData();

        $acyUserId = $decodedData['userId'] ?? '';
        $mailId = $decodedData['mailId'] ?? '';
        $sendDate = $decodedData['sendDate'] ?? time();
        $sendEmailOnlyForSpecificUser = $decodedData['sendEmailOnlyForSpecificUser'] ?? true;

        $queueClass = new QueueClass();

        if ($sendEmailOnlyForSpecificUser) {
            $insertedRows = $queueClass->addQueue($acyUserId, $mailId, acym_date($sendDate, 'Y-m-d H:i', false));
            if (empty($insertedRows)) {
                $this->sendJsonResponse(['message' => 'Error inserting email in the queue.', 'error' => $queueClass->errors], 500);
            }
            $this->sendJsonResponse(['message' => 'Email inserted in the queue for a specific user.']);
        }

        $mailClass = new MailClass();
        $mailToSend = $mailClass->getOneById($mailId);

        if (empty($mailToSend)) {
            $this->sendJsonResponse(['message' => 'Email not found.'], 404);
        }

        $mailToSend->sending_date = acym_date($sendDate, 'Y-m-d H:i', false);
        $mailToSend->parent_id = $mailId;
        $mailToSend->sending_params = [''];

        $insertedRows = $queueClass->queue($mailToSend);
        if (empty($insertedRows)) {
            $this->sendJsonResponse(['message' => 'Error inserting email in the queue.', 'error' => $queueClass->errors], 500);
        }
        $this->sendJsonResponse(['message' => 'Email inserted in the queue for the list the email is attached to.']);
    }

    public function sendEmailToSingleUser(): void
    {
        $decodedData = acym_getJsonData();

        if (empty($decodedData['email'])) {
            $this->sendJsonResponse(['message' => 'Receiver email address not provided in the request body.'], 422);
        }

        $mailer = new MailerHelper();
        $mailer->report = false;

        $email = $decodedData['email'];
        $userClass = new UserClass();
        $user = $userClass->getOneByEmail($email);
        if (empty($user)) {
            if (!empty($decodedData['autoAddUser'])) {
                $mailer->autoAddUser = true;
            } else {
                $this->sendJsonResponse(['message' => 'User doesn\'t exist'], 404);
            }
        }

        if (empty($decodedData['emailId'])) {
            $this->sendJsonResponse(['message' => 'Email ID not provided in the request body.'], 422);
        }

        $emailId = $decodedData['emailId'];
        $mailClass = new MailClass();
        $emailToSend = $mailClass->getOneById($emailId);
        if (empty($emailToSend)) {
            $this->sendJsonResponse(['message' => 'Email doesn\'t exist'], 404);
        }

        if (isset($decodedData['trackEmail'])) {
            $mailer->trackEmail = $decodedData['trackEmail'];
        }

        if (!empty($decodedData['params'])) {
            foreach ($decodedData['params'] as $key => $value) {
                $mailer->addParam($key, $value);
            }
        }

        try {
            $success = $mailer->sendOne($emailId, $email);
            if ($success) {
                $this->sendJsonResponse(['message' => 'Email sent successfully.']);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['message' => 'Error sending.'], 500);
        }

        $this->sendJsonResponse(['message' => 'Error sending.'], 500);
    }

    public function getEmails(): void
    {
        $filters = acym_getVar('array', 'filters', []);
        $typeMail = null;
        if (!empty($filters['type'])) {
            $typeMail = $filters['type'];

            if (!in_array($typeMail, MailClass::ALL_TYPES)) {
                $this->sendJsonResponse(['message' => 'Invalid type.'], 422);
            }
        }

        $mailClass = new MailClass();
        $mails = $mailClass->getMailsByType(
            $typeMail,
            [
                'offset' => acym_getVar('int', 'offset', 0),
                'mailsPerPage' => acym_getVar('int', 'limit', 100),
                'filters' => $filters,
            ]
        );

        foreach ($mails['mails'] as $i => $oneMail) {
            $mails['mails'][$i] = $this->removeExtraColumns(self::TYPE_MAIL, $oneMail);
        }

        $this->sendJsonResponse($mails['mails']);
    }
}
