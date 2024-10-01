<?php

namespace App\Services\Auth;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Gate as BaseGate;
use Illuminate\Auth\Access\Response;

class CustomGate extends BaseGate
{

    /**
     * В переопределённом методе изменён код
     * отказа с 403 на 401. Это сделано, т.к.
     * код 403 используется для уведомления
     * о просроченном токене. В ответ приложение
     * запрашивает свежий access токен.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return \Illuminate\Auth\Access\Response
     */
    public function inspect($ability, $arguments = [])
    {
        try {
            $result = $this->raw($ability, $arguments);

            if ($result instanceof Response) {
                return $result;
            }

            abort_if(!$result, 401, 'Unauthorized.');

            return Response::allow();
        } catch (AuthorizationException $e) {

            abort(401, $e->getMessage());
        }
    }
}
