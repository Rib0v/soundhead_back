<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StatusRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Resources\Order\IndexResource;
use App\Http\Resources\Order\ShowResource;
use App\Http\Resources\Order\UserResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /**
     * Список всех заказов
     * 
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return IndexResource::collection(Order::query()->paginate(20));
    }

    /**
     * Создание заказа
     * 
     * @param StoreRequest $request
     * @param OrderService $service
     * @return Response
     */
    public function store(StoreRequest $request, OrderService $service): Response
    {
        [$order, $orderProducts] = $service->getPreparedOrderAndProducts(
            order: $request->validated(),
            user: $request->user(),
        );

        $createdOrder = Order::create($order);
        $createdOrder->orderProducts()->createMany($orderProducts);

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
     * @return Response
     */
    public function changeStatus(Order $order, StatusRequest $request): Response
    {
        $order->status_id = $request->status;
        $order->save();

        return response(['message' => "Статус заказа #$order->id успешно изменён"]);
    }

    /**
     * Отображение заказа
     * 
     * @param Order $order
     * @return ShowResource
     */
    public function show(Order $order): ShowResource
    {
        return new ShowResource($order);
    }

    /**
     * Список заказов пользователя
     * 
     * @param int $user
     * @return ResourceCollection
     */
    public function showByUserId(int $user): ResourceCollection
    {
        $orders = Order::where('user_id', $user)->paginate(20);

        return UserResource::collection($orders);
    }

    /**
     * Удаление заказа
     * 
     * @param Order $order
     * @return Response
     */
    public function destroy(Order $order): Response
    {
        $order->delete();

        return response(['message' => 'Заказ удалён.', 'order' => $order]);
    }
}
