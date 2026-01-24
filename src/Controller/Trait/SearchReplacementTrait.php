<?php
namespace App\Controller\Trait;

use App\Entity\User;
use App\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Request;

trait SearchReplacementTrait
{
    private function getSearchParams(Request $request): array
    {
        $params = [];

        $region = $request->query->get('region', 'all');
        $region = strtolower($region) === 'all' ? null : (int) $region;

        $specialite = $request->query->get('specialite', 'all');
        $specialite = strtolower($specialite) === 'all' ? null : (int) $specialite;

        $search = $request->query->get('search');

        // region
        if (!empty($region)) {
            $params['region'] = $region;
        }

        // specialite
        if (!empty($specialite)) {
            $params['specialite'] = $specialite;
        }

        // search
        if (!empty($search)) {
            $params['search'] = $search;
        }

        $pagination = new Pagination(array_merge($request->query->all(), [
            'limit' => (int) $request->query->get('limit', 20)
        ]));
        $pagination->extraOptions = $params;

        // role
        $params['role'] = User::ROLE_REPLACEMENT_ID;

        return array_merge($pagination->toArray(), $params, [
            'pagination' => $pagination
        ]);
    }
}