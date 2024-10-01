<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StatusRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Resources\Order\IndexResource;
use App\Http\Resources\Order\ShowResource;
use App\Http\Resources\Order\UserResource;
use App\Models\Order;
use App\Models\Status;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * Список всех заказов
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function index(Request $request): Response
    {
        return response(IndexResource::collection(Order::query()->paginate(20)));
    }

    /**
     * Создание заказа
     * 
     * @param StoreRequest $request
     * @param OrderService $service
     * 
     * @return Response
     */
    public function store(StoreRequest $request, OrderService $service): Response
    {
        $validated = $request->validated();

        $validated['products'] = $service->addActualPricesFromDB($validated['products']);
        $preparedOrder = $service->prepareTheOrder($validated, $request);

        $createdOrder = Order::create($preparedOrder);
        $createdOrder->orderProducts()->createMany($validated['products']);

        return response([
            'message' => 'Заказ успешно создан',
            'order' => $createdOrder,
        ], 201);
    }

    /**
     * Изменение статуса заказа
     * 
     * @param Order $order
     * @param StatusRequest $request
     * @param OrderService $service
     * 
     * @return Response
     */
    public function changeStatus(Order $order, StatusRequest $request, OrderService $service): Response
    {
        $statusId = $request->validated('status');
        $checkedStatus = $service->checkStatusId($statusId);

        if (!$checkedStatus) return response(['message' => 'Указан id несуществующего статуса.'], 400);

        $order->status_id = $statusId;
        $order->save();

        return response(['message' => "Статус заказа #$order->id успешно изменён"]);
    }

    /**
     * Отображение заказа
     * 
     * @param Order $order
     * @param Request $request
     * 
     * @return Response
     */
    public function show(Order $order, Request $request): Response
    {
        return response(new ShowResource($order));
    }

    /**
     * Список заказов пользователя
     * 
     * @param int $user
     * 
     * @return Response
     */
    public function showByUserId(int $user): Response
    {
        $orders = Order::where('user_id', $user)->paginate(20);

        return response(UserResource::collection($orders));
    }

    /**
     * Удаление заказа
     * 
     * @param Order $order
     * @param Request $request
     * 
     * @return Response
     */
    public function destroy(Order $order, Request $request): Response
    {
        $order->delete();

        return response(['message' => 'Заказ удалён.', 'order' => $order]);
    }
}
