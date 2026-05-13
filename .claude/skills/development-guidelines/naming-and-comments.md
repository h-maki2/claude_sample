# 命名規則・コメントガイドライン

このファイルは `ddd-guidelines` スキルから参照される命名規則とコメントの指針です。

---

## 命名規則

### 大原則: ユビキタス言語に基づいて命名する

> クラス名・メソッド名・変数名・パッケージ名は、すべてユビキタス言語（`ubiquitous_language.csv`）の英語名に従う。

- 命名に迷ったらユビキタス言語一覧を確認する
- ユビキタス言語にない概念を実装する場合はチームで用語を決めてCSVに追加する
- 技術的な命名（`Manager`, `Handler`, `Helper`, `Utils`, `Data`）は避け、ドメインの言葉を使う

### クラス名の命名規則

| パターン | 命名例 | 規則 |
|---------|--------|------|
| エンティティ | `Reservation`, `MeetingRoom` | ユビキタス言語の英語名（パスカルケース） |
| 値オブジェクト | `ReservationName`, `ContactEmail`, `ReservationTimeRange` | ユビキタス言語の英語名（パスカルケース） |
| 集約ルート | `Reservation` | エンティティと同じ（ステレオタイプで区別） |
| ドメインサービス | `ReservationConflictChecker` | 責務を表す動詞+名詞（パスカルケース） |
| リポジトリI/F | `ReservationRepository` | `{集約名}Repository`（`Interface`サフィックス禁止） |
| リポジトリ実装 | `EloquentReservationRepository` | `{技術名}{集約名}Repository` |
| ユースケース | `CreateReservationUseCase` | `{動詞}{名詞}UseCase` |
| 仕様クラス | `ReservablePeriodSpecification` | `{条件名}Specification` |
| ドメインイベント | `ReservationCancelled` | `{名詞}{過去分詞}` |
| クエリサービスI/F | `ReservationListQuery` | `{名詞}{用途}Query`（`Interface`サフィックス禁止） |
| 参照系モデル | `ReservationListItem` | `{名詞}{用途}` |
| 腐敗防止層 | `ExternalCalendarAdapter` | `{外部システム名}Adapter` |

> **インターフェース命名の禁止事項**: インターフェース名に `Interface` サフィックスを付けてはならない。
> - NG: `OrderRepositoryInterface`, `ReservationRepositoryInterface`
> - OK: `OrderRepository`, `ReservationRepository`

### メソッド名の命名規則

**Command（更新系）**: ビジネスの意図を表す動詞を使う
- `cancel()`, `confirm()`, `reschedule()`, `extendPeriod()`

**Query（参照系）**: `find*`, `get*`, `list*`, `exists*`, `count*` を基本とする
```
findById(ReservationId $id): ?Reservation
existsOverlapping(MeetingRoomId $roomId, ReservationTimeRange $range): bool
```

**仕様クラス・ドメインサービス**: `isSatisfiedBy()`, `isOverlapping()`, `check*()`, `validate*()`, `calculate*()`

---

## コメントガイドライン

### 基本方針

> **コメントを追加したくなったとき、まずリファクタリングを試みる。**

1. メソッドを適切な名前で抽出できないか
2. 変数名を意図が明確な名前に変えられないか
3. 値オブジェクトや仕様クラスにロジックを移せないか

### 書くべきコメント

**意図の説明**: 「なぜこの実装にしたのか」を伝える。決定の背景・制約・ビジネスルールの根拠を残す。

```php
// 予約時間は30分単位でなければならないビジネスルール（仕様書 3.2節）
if ($minutes % 30 !== 0) {
    throw new InvalidArgumentException('予約は30分単位で設定してください');
}
```

```php
// CQS violation: ドメインイベントの発行パターン上、
// 取り出しと同時にクリアする必要があるため意図的にCQSを破っている
public function pullDomainEvents(): array { ... }
```

**警告**: テスト環境での差し替えが必要な外部連携など。

```php
// WARNING: この処理は外部カレンダーAPIに通知を送るため、
// テスト環境では必ずモックに差し替えること
```

### 書いてはいけないコメント

- コードを日本語で言い換えているだけのコメント
- 情報ゼロのDocコメント（PHPDocは外部公開APIのみ）
- コメントアウトされたコード（gitに残るので削除する）

---

## 命名チェックリスト

```
□ ユビキタス言語CSVを確認し、英語名が存在するか確認した
□ クラス名はドメインの概念を直接表しているか（Manager/Helper/Utilsを避けたか）
□ メソッド名はビジネスの意図を表しているか（技術的な動詞を避けたか）
□ Command系メソッドとQuery系メソッドを混在させていないか
□ コメントは「なぜ」を伝えているか（「何を」ではなく）
```
