import sqlite3, json

db = sqlite3.connect(r'c:\Users\hisha\.local\share\opencode\opencode.db')
c = db.cursor()

print('=== ACCOUNT TABLE ===')
try:
    c.execute('SELECT * FROM account')
    cols = [d[0] for d in c.description]
    rows = c.fetchall()
    for row in rows:
        r = dict(zip(cols, row))
        for k, v in r.items():
            if k in ('key', 'api_key', 'password', 'token', 'secret', 'access_key'):
                if v and len(str(v)) > 8:
                    r[k] = str(v)[:6] + '***' + str(v)[-4:]
        print(json.dumps(r, indent=2, ensure_ascii=False, default=str))
        print('---')
except Exception as e:
    print(f'ERROR account: {e}')

print()
print('=== CREDENTIAL TABLE ===')
try:
    c.execute('SELECT * FROM credential')
    cols = [d[0] for d in c.description]
    rows = c.fetchall()
    for row in rows:
        r = dict(zip(cols, row))
        for k, v in r.items():
            if k in ('key', 'api_key', 'password', 'token', 'secret', 'access_key', 'apiKey', 'value'):
                if v and len(str(v)) > 8:
                    r[k] = str(v)[:6] + '***' + str(v)[-4:]
        print(json.dumps(r, indent=2, ensure_ascii=False, default=str))
        print('---')
except Exception as e:
    print(f'ERROR credential: {e}')

print()
print('=== CONTROL_ACCOUNT TABLE ===')
try:
    c.execute('SELECT * FROM control_account')
    cols = [d[0] for d in c.description]
    rows = c.fetchall()
    for row in rows:
        r = dict(zip(cols, row))
        for k, v in r.items():
            if k.lower() in ('key', 'api_key', 'password', 'token', 'secret', 'access_key', 'apikey'):
                if v and len(str(v)) > 8:
                    r[k] = str(v)[:6] + '***' + str(v)[-4:]
        print(json.dumps(r, indent=2, ensure_ascii=False, default=str))
        print('---')
except Exception as e:
    print(f'ERROR control_account: {e}')

db.close()
