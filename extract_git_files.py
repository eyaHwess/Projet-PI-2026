#!/usr/bin/env python3
"""Extract files from git blob hashes and write them as proper UTF-8"""
import subprocess
import os
import sys

base = r'c:\piDev\Projet-PI-2026'

files_to_extract = {
    # Templates - reclamation
    'PI_dev/templates/reclamation/index.html.twig': '052a72dfd32a1d0ea94939284e2f4f0156da8ce9',
    'PI_dev/templates/reclamation/reclamation.html.twig': '0181595b32655e395098f5bbe7bfe11b00ac950f',
    'PI_dev/templates/reclamation/show.html.twig': '2b6ed7eb5f33fa128c5c6ab1f9c24af0f7f1a67a',
    'PI_dev/templates/admin_response/index.html.twig': '02afacea58e6bfce14c8efcfd83952b6dd1e8c13',
    'PI_dev/templates/admin_response/reply.html.twig': '529779aca0387a86518e8583bb71ada117251ee6',
    'PI_dev/templates/admin/components/reclamation/claim_modal.html.twig': '66299316177e01d7c11632c87ae5815f057c6083',
    'PI_dev/templates/admin/components/reclamation/claims.html.twig': '9438776c382332c1b806c03a684b64a66e6c4bf2',
}

# Also get PHP file hashes dynamically
php_files = [
    'PI_dev/src/Controller/ReclamationController.php',
    'PI_dev/src/Controller/AdminResponseController.php',
    'PI_dev/src/Entity/Reclamation.php',
    'PI_dev/src/Entity/Response.php',
    'PI_dev/src/Form/ReclamationType.php',
    'PI_dev/src/Form/ResponseType.php',
    'PI_dev/src/Service/ReclamationNotificationService.php',
    'PI_dev/src/Service/AIResponseService.php',
    'PI_dev/src/Enum/ReclamationStatusEnum.php',
    'PI_dev/src/Enum/ReclamationTypeEnum.php',
    'PI_dev/src/Repository/ReclamationRepository.php',
    'PI_dev/src/Repository/ResponseRepository.php',
]

for f in php_files:
    result = subprocess.run(
        ['git', 'ls-tree', 'origin/shaima2', f],
        capture_output=True, cwd=base
    )
    line = result.stdout.decode('utf-8').strip()
    if line:
        parts = line.split()
        if len(parts) >= 3:
            files_to_extract[f] = parts[2]

# Extract and write each file
fixed = 0
for rel_path, blob_hash in files_to_extract.items():
    full_path = os.path.join(base, rel_path.replace('/', os.sep))
    
    # Use git cat-file in binary mode
    result = subprocess.run(
        ['git', 'cat-file', '-p', blob_hash],
        capture_output=True, cwd=base  # raw bytes
    )
    
    if result.returncode != 0:
        print(f"ERROR getting {rel_path}: {result.stderr}")
        continue
    
    raw_bytes = result.stdout
    
    # The file should be UTF-8, write the raw bytes directly
    os.makedirs(os.path.dirname(full_path), exist_ok=True)
    with open(full_path, 'wb') as f:
        f.write(raw_bytes)
    
    # Verify
    try:
        text = raw_bytes.decode('utf-8')
        accented = sum(1 for c in text if ord(c) > 127 and ord(c) < 1000)
        print(f"OK: {rel_path} ({accented} accented chars)")
        fixed += 1
    except UnicodeDecodeError:
        print(f"WARNING: {rel_path} is not valid UTF-8")

print(f"\nDone! Extracted {fixed} files cleanly.")
