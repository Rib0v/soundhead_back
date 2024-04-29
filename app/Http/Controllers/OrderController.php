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
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *   tags={"Order"},
     *   path="/api/orders",
     *   summary="INDEX - Список всех заказов",
     *   security={ { "jwt": {} } },
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="total", type="integer", example=46200),
     *           @OA\Property(property="name", type="string", example="Семён Семёныч"),
     *           @OA\Property(property="user_id", type="integer", example=1),
     *           @OA\Property(property="phone", type="string", example="+79999999999"),
     *           @OA\Property(property="email", type="string", example="example@mail.org"),
     *           @OA\Property(property="address", type="string", example="ул. Кукурузная, д. 35"),
     *           @OA\Property(property="comment", type="string", example="Побыстрее!"),
     *           @OA\Property(property="status", type="string", example="Создан, ожидает подтверждения."),
     *         )
     *       ),
     *       @OA\Property(property="links", type="obj", example="{...}"),
     *       @OA\Property(property="meta", type="obj", example="{...}")
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы"),
     * )
     */
    public function index(Request $request)
    {
        if (Gate::denies('order-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return IndexResource::collection(Order::query()->paginate(20));
    }

    /**
     * @OA\Post(
     *   tags={"Order"},
     *   path="/api/orders",
     *   summary="STORE - Создание заказа",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="name", type="string", example="Василий Иваныч"),
     *       @OA\Property(property="phone", type="string", example="+70123456789"),
     *       @OA\Property(property="email", type="string", example="ivanich@mail.org"),
     *       @OA\Property(property="address", type="string", example="ул. Ленина, д. 1"),
     *       @OA\Property(property="comment", type="string", example="Хочу скидку побольше!"),
     *       @OA\Property(property="products", type="array", 
     *         @OA\Items(
     *           @OA\Property(property="product_id", type="integer", example=1),
     *           @OA\Property(property="count", type="integer", example=3),
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="OK",
     *     @OA\JsonContent(type="object",
     *       @OA\Property(property="message", type="string", example="Заказ успешно создан."),
     *       @OA\Property(property="order", type="obj", example="{ id, ... }"),
     *       @OA\Property(property="errors", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=422, description="Валидация не пройдена"),
     * )
     */
    public function store(StoreRequest $request, OrderService $service)
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
     * @OA\Patch(
     *   tags={"Order"},
     *   path="/api/orders/{order}/status",
     *   summary="changeStatus - Изменение статуса заказа",
     *   security={ { "jwt": {} } },
     *   @OA\Parameter(
     *     name="order",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Статус заказа #1 успешно изменён")
     *     )
     *   ),
     *   @OA\Response(response=400, description="Неверный id статуса",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Указан id несуществующего статуса.",
     *       )
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы")
     * )
     */
    public function changeStatus(Order $order, StatusRequest $request, OrderService $service)
    {
        if (Gate::denies('order-manager', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $statusId = $request->validated('status');
        $checkedStatus = $service->checkStatusId($statusId);

        if (!$checkedStatus) return response(['message' => 'Указан id несуществующего статуса.'], 400);

        $order->status_id = $statusId;
        $order->save();

        return response(['message' => "Статус заказа #$order->id успешно изменён"]);
    }

    /**
     * @OA\Get(
     *   tags={"Order"},
     *   path="/api/orders/{order}",
     *   summary="SHOW - Отображение заказа",
     *   security={{ "jwt" : {} }},
     *   @OA\Parameter(name="order", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="total", type="integer", example=46200),
     *         @OA\Property(property="name", type="string", example="Василий Иваныч"),
     *         @OA\Property(property="phone", type="string", example="+70123456789"),
     *         @OA\Property(property="email", type="string", example="example@mail.org"),
     *         @OA\Property(property="address", type="string", example="ул. Ленина, д. 1"),
     *         @OA\Property(property="comment", type="string", example="Поживее!"),
     *         @OA\Property(property="status", type="string", example="Подтверждён, ожидает оплаты."),
     *         @OA\Property(property="created_at", type="string", example="2024-04-09T20:08:18.000000Z"),
     *         @OA\Property(property="updated_at", type="string", example="2024-04-09T16:08:40.000000Z"),
     *         @OA\Property(property="products", type="array", 
     *           @OA\Items(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="JBL pariatur"),
     *             @OA\Property(property="slug", type="string", example="jbl-pariatur"),
     *             @OA\Property(property="image", type="string", example="http://localhost:8000/storage/photos/products/overhead/wired/0.jpg"),
     *             @OA\Property(property="count", type="integer", example=2),
     *             @OA\Property(property="price", type="integer", example=23300),
     *           )
     *         ),
     *       ),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы")
     * )
     */
    public function show(Order $order, Request $request)
    {
        if (Gate::denies('show', [$order, $request->bearerToken()])) {
            abort(404, 'Not found');
        }

        return new ShowResource($order);
    }

    /**
     * @OA\Get(
     *   tags={"Order"},
     *   path="/api/users/{id}/orders",
     *   summary="showByUserId - Список заказов пользователя",
     *   security={{ "jwt" : {} }},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="array",
     *         @OA\Items(
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="total", type="integer", example=46200),
     *           @OA\Property(property="address", type="string", example="ул. Ленина, д. 1"),
     *           @OA\Property(property="comment", type="string", example="Поживее!"),
     *           @OA\Property(property="status", type="string", example="Подтверждён, ожидает оплаты."),
     *           @OA\Property(property="created_at", type="string", example="2024-04-09T20:08:18.000000Z"),
     *           @OA\Property(property="updated_at", type="string", example="2024-04-09T16:08:40.000000Z"),
     *         )
     *       ),
     *       @OA\Property(property="links", type="obj", example="{...}"),
     *       @OA\Property(property="meta", type="obj", example="{...}"),
     *     )
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы")
     * )
     */
    public function showByUserId(int $id, Request $request, Order $order)
    {
        if (Gate::denies('showByUserId', [$order, $request->bearerToken(), $id])) {
            abort(404, 'Not found');
        }

        $orders = Order::where('user_id', $id)->paginate(20);
        return UserResource::collection($orders);
    }

    /**
     * @OA\Delete(
     *   tags={"Order"},
     *   path="/api/orders/{order}",
     *   summary="DESTROY - Удаление заказа",
     *   security={{ "jwt":{} }},
     *   @OA\Parameter(name="order", in="path", required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Заказ удалён."),
     *       @OA\Property(property="order", type="object", 
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="user_id", type="integer", example=1),
     *         @OA\Property(property="status_id", type="integer", example=1),
     *         @OA\Property(property="total", type="integer", example=46200),
     *         @OA\Property(property="name", type="string", example="Василий Иваныч"),
     *         @OA\Property(property="phone", type="string", example="+70123456789"),
     *         @OA\Property(property="email", type="string", example="ivanich@mail.org"),
     *         @OA\Property(property="address", type="string", example="ул. Ленина, д. 1"),
     *         @OA\Property(property="comment", type="string", example="Поживее!"),
     *         @OA\Property(property="created_at", type="string", example="2024-04-09T20:08:18.000000Z"),
     *         @OA\Property(property="updated_at", type="string", example="2024-04-09T16:08:40.000000Z")
     *       ),
     *     ),
     *   ),
     *   @OA\Response(response=404, description="Нет прав = не знаешь о существовании страницы")
     * )
     */
    public function destroy(Order $order, Request $request)
    {
        if (Gate::denies('admin', [$request->bearerToken()])) {
            abort(404, 'Not found');
        }

        $order->delete();

        return response(['message' => 'Заказ удалён.', 'order' => $order]);
    }
}
