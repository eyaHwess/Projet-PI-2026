#!/usr/bin/env python3
import subprocess, os

base = r'c:\piDev\Projet-PI-2026'

files_to_restore = [
    'PI_dev/templates/goal/edit.html.twig',
    'PI_dev/templates/goal/index.html.twig',
    'PI_dev/templates/goal/index_modern.html.twig',
    'PI_dev/templates/goal/new.html.twig',
    'PI_dev/templates/goal/show.html.twig',
    'PI_dev/templates/onboarding/index.html.twig',
    'PI_dev/templates/security/register.html.twig',
    'PI_dev/templates/user/ai_profile.html.twig',
    'PI_dev/src/Controller/GoalController.php',
]

for rel_path in files_to_restore:
    # Get blob hash from HEAD
    result = subprocess.run(
        ['git', 'ls-tree', 'HEAD', rel_path],
        capture_output=True, cwd=base
    )
    line = result.stdout.decode('utf-8').strip()
    if not line:
        print(f"NOT IN HEAD: {rel_path}")
        continue
    
    blob = line.split()[2]
    
    # Get raw bytes
    raw = subprocess.run(
        ['git', 'cat-file', '-p', blob],
        capture_output=True, cwd=base
    ).stdout
    
    full_path = os.path.join(base, rel_path.replace('/', os.sep))
    with open(full_path, 'wb') as f:
        f.write(raw)
    
    text = raw.decode('utf-8', errors='replace')
    accented = sum(1 for c in text if 128 <= ord(c) <= 1000)
    print(f"Restored: {rel_path} ({accented} accented chars)")

print("\nDone!")
