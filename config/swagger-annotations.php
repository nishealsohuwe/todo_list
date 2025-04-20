<?php
/**
 * @OA\Info(
 *     title="TodoList API",
 *     version="1.0.0",
 *     description="API для управления задачами",
 *     @OA\Contact(email="support@example.com")
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Локальный сервер разработки"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApiSpec {}