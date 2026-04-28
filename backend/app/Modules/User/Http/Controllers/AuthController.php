<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Application\UseCases\LoginUser;
use App\Modules\User\Application\UseCases\LogoutUser;
use App\Modules\User\Http\Requests\LoginRequest;
use App\Modules\User\Application\DTO\LoginDTO;
use Illuminate\Http\Request;
use App\Modules\User\Domain\Entities\StudentEntity;

class AuthController
{
    private LoginUser $loginUser;
    private LogoutUser $logoutUser;

    public function __construct(LoginUser $loginUser, LogoutUser $logoutUser)
    {
        $this->loginUser = $loginUser;
        $this->logoutUser = $logoutUser;
    }

    public function login(LoginRequest $request)
    {
        $loginDTO = $request->toDTO();
        $result = $this->loginUser->execute($loginDTO);

        $userEntity = $result['user'];
        $userData = [
            'id'         => $userEntity->getId(),
            'first_name' => $userEntity->getFirstName(),
            'last_name'  => $userEntity->getLastName(),
            'email'      => $userEntity->getEmail(),
            'role'       => $userEntity->getRole(),
            'status'     => $userEntity->getStatus(),
        ];

        if ($userEntity instanceof StudentEntity) {
            $userData['classroom_id'] = $userEntity->getClassroomId();
            $userData['squad_id']     = $userEntity->getSquadId();
        }

        return response()->json([
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
            'user'         => $userData,
        ]);
    }

    public function logout(Request $request)
    {
        $tokenStr = $request->bearerToken();
        $tokenId = $tokenStr ? explode('|', $tokenStr)[0] : '';
        
        if ($tokenId) {
            $this->logoutUser->execute($request->user()->id, $tokenId);
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
