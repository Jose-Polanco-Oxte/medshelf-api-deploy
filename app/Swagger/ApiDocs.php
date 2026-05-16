<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Medshelf API",
 *     version="1.0.0",
 *     description="OpenAPI documentation for the Medshelf API."
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="Base URL"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth")
 * @OA\Tag(name="Houses")
 * @OA\Tag(name="Places")
 * @OA\Tag(name="Items")
 * @OA\Tag(name="Consumptions")
 * @OA\Tag(name="Products")
 * @OA\Tag(name="Profiles")
 * @OA\Tag(name="Treatments")
 * @OA\Tag(name="ActiveIngredients")
 * @OA\Tag(name="PharmaceuticalForms")
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     required={"message","timestamp"},
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="timestamp", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="MessageResponse",
 *     type="object",
 *     required={"message"},
 *     @OA\Property(property="message", type="string")
 * )
 * @OA\Schema(
 *     schema="AuthResponse",
 *     type="object",
 *     required={"expiresIn","user"},
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         required={"id","name","email"},
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string", format="email")
 *     ),
 *     @OA\Property(property="expiresIn", type="integer", description="Token lifetime in seconds"),
 *     @OA\Property(
 *         property="house",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     )
 * )
 * @OA\Schema(
 *     schema="CursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="OffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="ItemCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ItemOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ItemResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="PlaceCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlaceResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PlaceOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlaceResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="ProductCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProductResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ProductOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProductResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="ProfileCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProfileResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ProfileOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ProfileResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="TreatmentCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="TreatmentOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/TreatmentResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="ConsumptionCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ConsumptionOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ConsumptionResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="ActiveIngredientCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ActiveIngredientResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="ActiveIngredientOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/ActiveIngredientResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="PharmaceuticalFormCursorPaginationResponse",
 *     type="object",
 *     required={"items","nextCursor"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PharmaceuticalFormResponse")),
 *     @OA\Property(property="nextCursor", type="string", nullable=true)
 * )
 * @OA\Schema(
 *     schema="PharmaceuticalFormOffsetPaginationResponse",
 *     type="object",
 *     required={"items","totalCount","page","size","hasMorePages"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PharmaceuticalFormResponse")),
 *     @OA\Property(property="totalCount", type="integer", minimum=0),
 *     @OA\Property(property="page", type="integer", minimum=1),
 *     @OA\Property(property="size", type="integer", minimum=1, maximum=100),
 *     @OA\Property(property="hasMorePages", type="boolean")
 * )
 * @OA\Schema(
 *     schema="HouseResponse",
 *     type="object",
 *     required={"id","owner","name","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="owner", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string")),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="PlaceResponse",
 *     type="object",
 *     required={"id","house","name","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="house", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="ItemResponse",
 *     type="object",
 *     required={"id","product","place","totalContent","expirationDate","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="product", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="place", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="totalContent", type="number"),
 *     @OA\Property(property="expirationDate", type="string", format="date"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="ItemView",
 *     type="object",
 *     required={"id","product","place","availableContent","expirationDate","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="product", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string")),
 *     @OA\Property(property="place", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string")),
 *     @OA\Property(property="availableContent", type="number"),
 *     @OA\Property(property="expirationDate", type="string", format="date")
 * )
 * @OA\Schema(
 *     schema="ItemDetail",
 *     type="object",
 *     required={"id","product","place","availableContent","expirationDate","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="product", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string"), @OA\Property(property="netContent", type="object", @OA\Property(property="value", type="number"), @OA\Property(property="unit", type="string")), @OA\Property(property="totalQuantity", type="number", nullable=true), @OA\Property(property="pharmaceuticalForm", type="object", @OA\Property(property="name", type="string"), @OA\Property(property="consumptionType", type="string"))),
 *     @OA\Property(property="place", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string")),
 *     @OA\Property(property="availableContent", type="number"),
 *     @OA\Property(property="expirationDate", type="string", format="date"),
 *     @OA\Property(property="createdAt", type="string", format="date")
 * )
 * @OA\Schema(
 *     schema="ConsumptionResponse",
 *     type="object",
 *     required={"id","item","amount","consumedAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="item", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="amount", type="number"),
 *     @OA\Property(property="consumedAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="ProfileResponse",
 *     type="object",
 *     required={"id","name","birthDate","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="relationship", type="string", nullable=true),
 *     @OA\Property(property="birthDate", type="string", format="date"),
 *     @OA\Property(property="allergies", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="MeResponse",
 *     type="object",
 *     required={"user","house"},
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *          required={"id","name","email"},
 *          @OA\Property(property="id", type="string", format="uuid"),
 *          @OA\Property(property="name", type="string"),
 *          @OA\Property(property="email", type="string")
 *      ),
 *      @OA\Property(
 *          property="house",
 *          type="object",
 *          nullable=true,
 *          @OA\Property(property="id", type="string", format="uuid"),
 *          @OA\Property(property="name", type="string")
 *      )
 * )
 * @OA\Schema(
 *     schema="ActiveIngredientResponse",
 *     type="object",
 *     required={"id","name","createdAt"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="PharmaceuticalFormResponse",
 *     type="object",
 *     required={"id","name","consumptionType","createdAt"},
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="consumptionType", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="ProductResponse",
 *     type="object",
 *     required={"id","name","pharmaceuticalForm","createdAt","composition"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="netContent", type="object", @OA\Property(property="value", type="number"), @OA\Property(property="unit", type="string")),
 *     @OA\Property(property="totalQuantity", type="number", nullable=true),
 *     @OA\Property(property="pharmaceuticalForm", type="object", @OA\Property(property="name", type="string"), @OA\Property(property="consumptionType", type="string")),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="composition", type="object")
 * )
 * @OA\Schema(
 *     schema="TreatmentResponse",
 *     type="object",
 *     required={"id","profile","item","status","dose","frequencyUnit","startDate","createdAt"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="profile", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="item", type="object", @OA\Property(property="id", type="string", format="uuid")),
 *     @OA\Property(property="status", type="string", enum={"active","paused","completed","cancelled"}),
 *     @OA\Property(property="dose", type="number", minimum=0.01),
 *     @OA\Property(property="frequencyUnit", type="string", enum={"hours","days","weeks"}),
 *     @OA\Property(property="startDate", type="string", format="date", example="2026-05-11"),
 *     @OA\Property(property="endDate", type="string", format="date", nullable=true, example="2026-05-30"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 * @OA\Schema(
 *     schema="TreatmentView",
 *     type="object",
 *     required={"id","profile","item","status","dose","frequencyUnit","startDate"},
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="profile", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string")),
 *     @OA\Property(property="item", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="product", type="object", @OA\Property(property="id", type="string", format="uuid"), @OA\Property(property="name", type="string"))),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="dose", type="number"),
 *     @OA\Property(property="frequencyUnit", type="string"),
 *     @OA\Property(property="startDate", type="string", format="date"),
 *     @OA\Property(property="endDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 */
final class ApiDocs
{
}

