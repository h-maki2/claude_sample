# テストアンチパターン

テストコードが完成したら、以下の項目を1つずつ確認すること。

---

## ユースケース・フィーチャーテストで Factory + save() を直接組み合わせない

前提データ（Given）の用意には `TestDataCreator` を使う。ファクトリとリポジトリの `save()` をテストメソッド内で直接組み合わせることは禁止。`TestDataCreator` が存在しない場合は作成してから使う。

```php
// NG
$existingRoom = TestMeetingRoomFactory::create(name: new MeetingRoomName('第1会議室'), ...);
$this->meetingRoomRepository->save($existingRoom); // ← 直接 save は禁止

// OK
$existingRoom = $this->meetingRoomTestDataCreator->create(name: new MeetingRoomName('第1会議室'), ...);
```

> リポジトリ永続化テスト（`Eloquent*RepositoryTest`）は別ルール。`save()` 自体がテスト対象のため Factory + `save()` の直接呼び出しが**正しいパターン**。

---

## Given と Then でリテラルを重複させない

同じリテラル値が Given と Then の両方に出てきたら変数化して共有する。

```php
// NG
$input = new CreateMeetingRoomInput(name: '第1会議室', capacity: 10);
$this->assertSame('第1会議室', $saved->name->value); // 重複

// OK
$name = '第1会議室';
$capacity = 10;
$input = new CreateMeetingRoomInput(name: $name, capacity: $capacity);
$this->assertSame($name, $saved->name->value);
```

---

## Enum のケース数をテストしない

将来ケースが追加されるたびにテストが壊れるが、それはビジネスロジックの問題ではない。

```php
// NG
$this->assertCount(4, Equipment::cases());
```

---

## ドメイン制約をユースケーステストで再テストしない

`{集約名}Test` で検証済みのバリデーション・不変条件はユースケーステストに書かない。ユースケーステストが検証すべきはユースケース固有の振る舞い（存在しないエンティティ・権限・リポジトリとのやりとり）。

```php
// NG: MeetingRoomTest で検証済みのドメイン制約をユースケーステストで再テスト
public function test_重複した備品を渡した場合は登録に失敗する(): void { ... }

// OK: ユースケース固有の振る舞い
public function test_存在しない会議室IDを指定した場合は編集に失敗する(): void { ... }
```

---

## Then フェーズにロジックを書かない

`array_map` / `foreach` / `for` などのロジックを Then に書かない。期待値はべた書きで記述する。順序が関係ない配列には `assertEqualsCanonicalizing` を使う。

```php
// NG
$returnedIds = array_map(fn($item) => $item->meetingRoomId, $result);
$this->assertContains('01957b3c-...', $returnedIds);

// OK
$this->assertEqualsCanonicalizing(
    ['01957b3c-1234-7abc-8def-000000000001', '01957b3c-1234-7abc-8def-000000000002'],
    [$result[0]->meetingRoomId, $result[1]->meetingRoomId],
);
```

---

## テストメソッド名を実装の詳細に依存させない

プロパティ名・メソッド名・例外クラス名はメソッド名に含めない。

```php
// NG: 例外クラス名を含んでいる
public function test_終了時刻が開始時刻より前の場合はInvalidArgumentExceptionが発生する(): void

// NG: プロパティ名 `value` を含んでいる
public function test_プロパティvalueで文字列値を取得できる(): void

// OK
public function test_終了時刻が開始時刻より前の場合は例外が発生する(): void
public function test_メールアドレスの文字列値を取得できる(): void
```

---

## PHP 標準の動作をテストしない

`tryFrom` / `from` など PHP 標準の動作はテストしない。上位レイヤー（UseCase など）を経由していても実質的に PHP 仕様をテストしているだけであれば同様に NG。

```php
// NG
$equipment = Equipment::tryFrom(99);
$this->assertNull($equipment);
```

---

## 正常生成テストで検証済みの値を再テストしない

`test_有効な値で〇〇を生成できる` で全プロパティをアサートしていれば、プロパティアクセスだけをテストする追加テストは不要。

```php
// NG: 正常生成テストで同一性は既に保証されている
public function test_meetingRoomIdプロパティで会議室IDに直接アクセスできる(): void { ... }
```
