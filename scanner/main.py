# scanner/main.py
import os
import pymysql
from datetime import datetime
import yaml

with open('config.yaml') as f:
    config = yaml.safe_load(f)

def is_new_folder(foldername):
    return foldername.lower() == "new"

def connect_db():
    return pymysql.connect(
        host=config['db']['host'],
        user=config['db']['user'],
        password=config['db']['password'],
        database=config['db']['database']
    )

def scan_files():
    scanned = 0
    skipped = 0
    current_year = datetime.now().year

    conn = connect_db()
    cursor = conn.cursor()

    for base_path in config['paths']:
        if not os.path.exists(base_path):
            print(f"❌ Tidak ditemukan: {base_path}")
            continue

        region_name = os.path.basename(base_path).lower()
        is_flat = region_name in config['flat_regions']

        if is_flat:
            partner_folders = [os.path.join(base_path, d) for d in os.listdir(base_path) if os.path.isdir(os.path.join(base_path, d))]
            region_id = get_or_create(cursor, 'regions', 'name', region_name)

            for partner_path in partner_folders:
                partner_name = os.path.basename(partner_path)
                partner_id = get_or_create(cursor, 'partners', 'name', partner_name, region_id)

                new_path = find_new_folder(partner_path)
                if not new_path:
                    print(f"❌ 'New' tidak ditemukan di {partner_path}")
                    continue

                scanned, skipped = scan_incoming_files(cursor, new_path, region_id, partner_id, current_year, scanned, skipped)

        else:
            for region_path in os.listdir(base_path):
                full_region_path = os.path.join(base_path, region_path)
                if not os.path.isdir(full_region_path): continue

                region_id = get_or_create(cursor, 'regions', 'name', region_path)

                for partner_path in os.listdir(full_region_path):
                    full_partner_path = os.path.join(full_region_path, partner_path)
                    if not os.path.isdir(full_partner_path): continue

                    partner_id = get_or_create(cursor, 'partners', 'name', partner_path, region_id)
                    new_path = find_new_folder(full_partner_path)
                    if not new_path:
                        print(f"❌ 'New' tidak ditemukan di {full_partner_path}")
                        continue

                    scanned, skipped = scan_incoming_files(cursor, new_path, region_id, partner_id, current_year, scanned, skipped)

    conn.commit()
    conn.close()
    print(f"✅ Selesai. Ditambahkan: {scanned}, Di-skip: {skipped}")

def get_or_create(cursor, table, key_field, value, region_id=None):
    if region_id:
        cursor.execute(f"SELECT id FROM {table} WHERE {key_field} = %s AND region_id = %s", (value, region_id))
    else:
        cursor.execute(f"SELECT id FROM {table} WHERE {key_field} = %s", (value,))
    row = cursor.fetchone()
    if row:
        return row[0]

    if region_id:
        cursor.execute(f"INSERT INTO {table} ({key_field}, region_id) VALUES (%s, %s)", (value, region_id))
    else:
        cursor.execute(f"INSERT INTO {table} ({key_field}) VALUES (%s)", (value,))
    return cursor.lastrowid

def find_new_folder(base_path):
    for d in os.listdir(base_path):
        full = os.path.join(base_path, d)
        if os.path.isdir(full) and is_new_folder(d):
            return full
    return None

def scan_incoming_files(cursor, path, region_id, partner_id, current_year, scanned, skipped):
    for f in os.listdir(path):
        file_path = os.path.join(path, f)
        if not os.path.isfile(file_path): continue
        mod_time = datetime.fromtimestamp(os.path.getmtime(file_path))
        if mod_time.year != current_year: continue

        cursor.execute("""
            SELECT COUNT(*) FROM incoming_files
            WHERE filename = %s AND region_id = %s AND partner_id = %s
        """, (f, region_id, partner_id))
        if cursor.fetchone()[0] == 0:
            cursor.execute("""
                INSERT INTO incoming_files (partner_id, region_id, filename, path, detected_at)
                VALUES (%s, %s, %s, %s, %s)
            """, (f, file_path, region_id, partner_id, mod_time))
            print(f"📥 Baru: {f} ({region_id}/{partner_id})")
            scanned += 1
        else:
            print(f"⏭️  Skip: {f} ({region_id}/{partner_id})")
            skipped += 1
    return scanned, skipped

if __name__ == "__main__":
    scan_files()
