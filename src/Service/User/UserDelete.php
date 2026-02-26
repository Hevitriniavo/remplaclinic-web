<?php

namespace App\Service\User;

use App\Common\IdUtil;
use App\Entity\User;
use App\Service\FileCleaner;
use Doctrine\DBAL\Connection;
use Exception;

class UserDelete
{
    public function __construct(
        private Connection $db,
        private UserService $userService,
        private FileCleaner $fileCleaner,
    ) {}
    
    public function remove(int $id)
    {
        /**
         * @var User
         */
        $user = $this->userService->getUser($id);

        $this->db->beginTransaction();
        try {

            $this->deleteUser($user);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();

            throw $e;
        }

        $this->fileCleaner->remove($user->getCv());
        $this->fileCleaner->remove($user->getDiplom());
        $this->fileCleaner->remove($user->getLicence());

        return true;
    }

    public function removeMultiple(array $ids)
    {
        /**
         * @var User[]
         */
        $users = $this->userService->getUsers($ids);

        $this->db->beginTransaction();
        try {

            foreach($users as $user) {
                $this->deleteUser($user);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();

            throw $e;
        }

        foreach($users as $user) {
            $this->fileCleaner->remove($user->getCv());
            $this->fileCleaner->remove($user->getDiplom());
            $this->fileCleaner->remove($user->getLicence());
        }

        return true;
    }

    private function deleteUser(User $user)
    {

        // delete reset password token
        $this->db->delete('user_reset_password_token', [
            'user_id' => $user->getId(),
        ]);

        // delete request response
        $this->db->delete('request_response', [
            'user_id' => $user->getId(),
        ]);

        // delete user region
        $this->db->delete('user_region', [
            'user_id' => $user->getId(),
        ]);

        // delete user speciality
        $this->db->delete('user_speciality', [
            'user_id' => $user->getId(),
        ]);

        // delete user user_user_role
        $this->db->delete('user_user_role', [
            'user_id' => $user->getId(),
        ]);

        // delete request
        $requestsId = $this->db
            ->executeQuery('SELECT id FROM request WHERE applicant_id = ?', [$user->getId()])
            ->fetchFirstColumn();
        if (!empty($requestsId)) {

            $ids = IdUtil::implode($requestsId, ', ');

            // delete request history
            $this->db->executeStatement('DELETE FROM request_history WHERE request_id IN ('. $ids . ')');

            // delete request response
            $this->db->executeStatement('DELETE FROM request_response WHERE request_id IN ('. $ids . ')');
            
            // delete request reason
            $this->db->executeStatement('DELETE FROM request_reason WHERE request_id IN ('. $ids . ')');
            
            // delete request speciality
            $this->db->executeStatement('DELETE FROM request_speciality WHERE request_id IN ('. $ids . ')');

            $this->db->delete('request', [
                'applicant_id' => $user->getId(),
            ]);
        }

        // delete user
        $this->db->delete('user', [
            'id' => $user->getId(),
        ]);

        // delete user address
        if (!is_null($user->getAddress())) {
            $this->db->delete('user_address', [
                'id' => $user->getAddress()->getId(),
            ]);
        }
        
        // delete user user_establishment
        if (!is_null($user->getEstablishment())) {
            $this->db->delete('user_establishment', [
                'id' => $user->getEstablishment()->getId(),
            ]);
        }

        // delete user user_subscription
        if (!is_null($user->getSubscription())) {
            $this->db->delete('user_subscription', [
                'id' => $user->getSubscription()->getId(),
            ]);
        }
    }
}