# プレゼンター層の実装パターン

プレゼンテーション層は **Presenter・View・JsonResponseFormatter** の3クラスで構成する。

## 責務の分割

| クラス | 責務 |
|--------|------|
| `<ユースケース名>Presenter` | ユースケースのResponseオブジェクトをビュー向けに変換する（フォーマット・マスキング・null変換等） |
| `Json<ユースケース名>View` | PresenterからJSONレスポンスを組み立てて返す |
| `JsonResponseFormatter` | 成功・エラーの共通JSONエンベロープを生成する（サブドメイン横断の共通クラス） |

---

## Presenterクラス

ユースケースの Response を受け取り、画面表示用にフォーマットした値を提供するクラス。ビジネスロジックは含まない。

```php
namespace Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms;

class ListMeetingRoomsPresenter
{
    public function __construct(
        private ListMeetingRoomsResponse $response
    ) {}

    public function getRoomCount(): string
    {
        return $this->response->totalCount . '件';
    }

    public function getFormattedCapacity(int $capacity): string
    {
        return $capacity . '名';
    }

    // 複雑な変換は専用のFormatterクラスに委譲する
    public function getDisplayStatus(): string
    {
        return MeetingRoomStatusLabelFormatter::format($this->response->statusCode);
    }
}
```

**実装ルール:**
- コンストラクタでユースケースの Response オブジェクトを受け取る（プリミティブのバラ渡しは NG）
- メソッド名は `get〜` / `is〜` / `has〜` / `getFormatted〜` / `getDisplay〜` / `getMasked〜` など、表示の意図が分かる命名にする
- 複雑なフォーマット・変換ロジックは専用の `〜Formatter`クラスなどに委譲する

---

## Viewクラス

Presenter を受け取り、HTTP レスポンスを返すクラス。フォーマット処理は持たない。

```php
namespace Modules\MeetingRoomManagement\Http\Presenters\ListMeetingRooms;

use Illuminate\Http\JsonResponse;

class JsonListMeetingRoomsView
{
    public function response(ListMeetingRoomsPresenter $presenter): JsonResponse
    {
        $jsonResponseArray = JsonResponseFormatter::success(
            $this->convertPresenterToResponseArray($presenter)
        );

        return response()->json($jsonResponseArray, 200);
    }

    private function convertPresenterToResponseArray(ListMeetingRoomsPresenter $presenter): array
    {
        return [
            'roomCount' => $presenter->getRoomCount(),
            'displayStatus' => $presenter->getDisplayStatus(),
        ];
    }
}
```

**実装ルール:**
- `response()` メソッドの引数は対応する Presenter のみ（Response を直接受け取らない）
- レスポンス配列の組み立ては `convertPresenterToResponseArray()` にまとめる
- HTTP ステータスコードは `response()` 内で決定する

---

## JsonResponseFormatter（共通クラス）

全サブドメインで共用するレスポンスエンベロープ生成クラス。`src/app/Http/Presenters/Shared/` に配置する。
namespace は `App\Http\Presenters\Shared`。

```php
namespace App\Http\Presenters\Shared;

class JsonResponseFormatter
{
    public static function success(
        array $responseData,
        string $statusMessage = 'success',
        string $message = '成功しました。'
    ): array {
        return [
            'status' => $statusMessage,
            'message' => $message,
            'data' => $responseData,
        ];
    }

    public static function error(
        array $responseData,
        string $statusMessage = 'error',
        string $message = 'エラーが発生しました。'
    ): array {
        return [
            'status' => $statusMessage,
            'message' => $message,
            'errors' => $responseData,
        ];
    }
}
```

---

## コントローラとの連携

コントローラは UseCase を呼び出し、その Response を Presenter に渡し、View 経由でレスポンスを返す。

```php
namespace Modules\MeetingRoomManagement\Http\Controllers;

class ListMeetingRoomsController
{
    public function __construct(
        private ListMeetingRoomsUseCase $useCase
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->useCase->execute();
        $presenter = new ListMeetingRoomsPresenter($response);
        return (new JsonListMeetingRoomsView())->response($presenter);
    }
}
```
