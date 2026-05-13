import argparse
import csv
import sys

# ==========================================
# 設定：CSVのカラム名（実際のCSVに合わせて変更してください）
# ==========================================
COL_DOMAIN = "ビジネスサブドメイン"
COL_DOMAIN_EN = "ビジネスサブドメイン英名"
COL_JA_NAME = "日本語名"
COL_EN_NAME = "英語名"
COL_MEANING = "意味"
COL_USEAGE = "使い方"

def read_csv_with_fallback(csv_path):
    """
    複数の文字コードを順番に試し、CSVファイルを読み込む
    """
    encodings = ['utf-8', 'utf-8-sig', 'cp932', 'shift_jis', 'euc_jp']
    
    for enc in encodings:
        try:
            with open(csv_path, mode='r', encoding=enc, newline='') as f:
                # 読み込みテスト
                f.read(1024)
                f.seek(0)
                
                # 辞書形式で読み込む
                reader = csv.DictReader(f)
                return list(reader)
        except UnicodeDecodeError:
            continue
        except FileNotFoundError:
            print(f"エラー: CSVファイルが見つかりません: {csv_path}", file=sys.stderr)
            sys.exit(1)
        except Exception as e:
            print(f"エラー: CSVの読み込み中に予期せぬエラーが発生しました: {e}", file=sys.stderr)
            sys.exit(1)
            
    print(f"エラー: 対応している文字コード（{', '.join(encodings)}）でファイルを読み込めませんでした。", file=sys.stderr)
    sys.exit(1)

def main():
    parser = argparse.ArgumentParser(description="ユビキタス言語を検索するスクリプト")
    parser.add_argument("--csv-path", required=True, help="対象のCSVファイルのパス")
    parser.add_argument("--domain", required=True, help="検索するビジネスサブドメイン")
    parser.add_argument("--name", required=False, help="検索するユビキタス言語名（日本語 または 英語）")

    args = parser.parse_args()

    # CSVの読み込み
    rows = read_csv_with_fallback(args.csv_path)

    if not rows:
        print("CSVファイルが空か、正しく読み取れませんでした。")
        sys.exit(1)

    # 検索用の入力値を小文字に変換（揺らぎ吸収）
    target_domain = args.domain.strip().lower()
    target_name = args.name.strip().lower() if args.name else None

    results = []

    for row in rows:
        # カラムが存在するかチェック
        if COL_DOMAIN not in row or COL_JA_NAME not in row or COL_EN_NAME not in row or COL_MEANING not in row:
            print(f"エラー: CSVに期待されるカラムが存在しません。必要なカラム: {COL_DOMAIN}, {COL_JA_NAME}, {COL_EN_NAME}, {COL_MEANING}", file=sys.stderr)
            sys.exit(1)

        # CSVの値を取得し、小文字に変換（Noneの場合は空文字にする）
        row_domain = str(row[COL_DOMAIN]).strip().lower()
        row_domain_en = str(row.get(COL_DOMAIN_EN, "")).strip().lower()
        row_ja_name = str(row[COL_JA_NAME]).strip().lower()
        row_en_name = str(row[COL_EN_NAME]).strip().lower()

        # モード1 & モード2 共通: ビジネスサブドメインの一致確認（日本語名 or 英語名）
        if row_domain == target_domain or row_domain_en == target_domain:
            if target_name:
                # モード2: 言語名（日本語 or 英語）の一致確認
                if row_ja_name == target_name or row_en_name == target_name:
                    results.append(row)
            else:
                # モード1: ドメイン一致で全て追加
                results.append(row)

    # 結果の出力
    if not results:
        if target_name:
            print(f"指定されたビジネスサブドメイン「{args.domain}」内に、用語「{args.name}」は見つかりませんでした。")
        else:
            print(f"指定されたビジネスサブドメイン「{args.domain}」に属する用語は見つかりませんでした。")
        return
    
    for item in results:
        print(f"【{item[COL_JA_NAME]}】({item[COL_EN_NAME]})")
        print(f"ビジネスサブドメイン : {item[COL_DOMAIN]}")
        print(f"意味       :\n{item[COL_MEANING]}")
        print(f"使い方     :\n{item[COL_USEAGE]}")
        print("-" * 50)

if __name__ == "__main__":
    main()