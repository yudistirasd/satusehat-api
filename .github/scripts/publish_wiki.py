import os
import re
import sys
import shutil

def rewrite_links(content, file_path, docs_dir, path_to_wiki_name):
    # Regex to find markdown links: [text](link)
    # We want to match: [text](target) where target doesn't start with http/https/mailto
    # We should handle anchors as well.
    pattern = r'\[([^\]]+)\]\(([^)]+)\)'
    
    file_dir = os.path.dirname(file_path)
    
    def replace_match(match):
        text = match.group(1)
        link = match.group(2)
        
        # Skip external links and anchors only
        if link.startswith(('http://', 'https://', 'mailto:', 'ftp:')) or link.startswith('#'):
            return f'[{text}]({link})'
        
        # Separate path and anchor
        parts = link.split('#', 1)
        rel_path = parts[0]
        anchor = '#' + parts[1] if len(parts) > 1 else ''
        
        if not rel_path:
            return f'[{text}]({link})'
            
        # Resolve path
        abs_path = os.path.normpath(os.path.join(file_dir, rel_path))
        
        # Check if the resolved path is in our mapping
        if abs_path in path_to_wiki_name:
            wiki_name = path_to_wiki_name[abs_path]
            # Replace markdown link with the wiki name (without .md extension, or with .md)
            # GitHub wiki handles page names without extension, e.g. [Patient](patient)
            # Let's strip the '.md' extension for wiki pages
            wiki_link = wiki_name[:-3] if wiki_name.endswith('.md') else wiki_name
            return f'[{text}]({wiki_link}{anchor})'
            
        return f'[{text}]({link})'
        
    return re.sub(pattern, replace_match, content)

def main():
    if len(sys.argv) < 3:
        print("Usage: python publish_wiki.py <docs_dir> <wiki_dir>")
        sys.exit(1)
        
    docs_dir = os.path.abspath(sys.argv[1])
    wiki_dir = os.path.abspath(sys.argv[2])
    
    if not os.path.exists(docs_dir):
        print(f"Error: docs_dir '{docs_dir}' does not exist.")
        sys.exit(1)
        
    if not os.path.exists(wiki_dir):
        print(f"Error: wiki_dir '{wiki_dir}' does not exist.")
        sys.exit(1)
        
    # Walk docs_dir and find all files
    all_files = []
    for root, dirs, files in os.walk(docs_dir):
        for f in files:
            all_files.append(os.path.join(root, f))
            
    # Build mapping from absolute path to wiki filename
    path_to_wiki_name = {}
    for f in all_files:
        rel_to_docs = os.path.relpath(f, docs_dir)
        basename = os.path.basename(f)
        
        if basename.lower() == 'readme.md':
            # Rename README.md to Home.md for wiki homepage
            wiki_name = 'Home.md'
        else:
            wiki_name = basename
            
        path_to_wiki_name[f] = wiki_name

    # Clear existing files in wiki_dir (except .git)
    for name in os.listdir(wiki_dir):
        if name == '.git':
            continue
        path = os.path.join(wiki_dir, name)
        if os.path.isdir(path):
            shutil.rmtree(path)
        else:
            os.remove(path)
            
    # Copy files and rewrite links for markdown files
    for f in all_files:
        wiki_name = path_to_wiki_name[f]
        dest_path = os.path.join(wiki_dir, wiki_name)
        
        # Make sure parent directory exists (wiki is flat, but just in case)
        os.makedirs(os.path.dirname(dest_path), exist_ok=True)
        
        if f.endswith('.md'):
            with open(f, 'r', encoding='utf-8') as file_in:
                content = file_in.read()
            
            rewritten_content = rewrite_links(content, f, docs_dir, path_to_wiki_name)
            
            with open(dest_path, 'w', encoding='utf-8') as file_out:
                file_out.write(rewritten_content)
        else:
            # For non-markdown files (images, etc.), copy directly
            shutil.copy2(f, dest_path)
            
    print(f"Successfully processed {len(all_files)} files into {wiki_dir}")

if __name__ == '__main__':
    main()
