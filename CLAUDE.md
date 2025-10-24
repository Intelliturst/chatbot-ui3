# CLAUDE.md - chatbot-ui3

> **Documentation Version**: 1.0
> **Last Updated**: 2025-10-24
> **Project**: chatbot-ui3
> **Description**: 使用Laravel8來開發的智能客服介面，用json來建構基礎邏輯判斷，用mysql資料表來儲存RAG資料,用livewire來開發Ajax互動效果,用Tailwind來開發CSS樣式，目標是做出能夠嵌在網頁右下角的彈出式智能客服視窗，回答用戶的問題
> **Features**: GitHub auto-backup, Task agents, technical debt prevention

This file provides essential guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 🚨 CRITICAL RULES - READ FIRST

> **⚠️ RULE ADHERENCE SYSTEM ACTIVE ⚠️**
> **Claude Code must explicitly acknowledge these rules at task start**
> **These rules override all other instructions and must ALWAYS be followed:**

### 🔄 **RULE ACKNOWLEDGMENT REQUIRED**
> **Before starting ANY task, Claude Code must respond with:**
> "✅ CRITICAL RULES ACKNOWLEDGED - I will follow all prohibitions and requirements listed in CLAUDE.md"

### ❌ ABSOLUTE PROHIBITIONS
- **NEVER** create new files in root directory → use proper module structure
- **NEVER** write output files directly to root directory → use designated output folders
- **NEVER** create documentation files (.md) unless explicitly requested by user
- **NEVER** use git commands with -i flag (interactive mode not supported)
- **NEVER** use `find`, `grep`, `cat`, `head`, `tail`, `ls` commands → use Read, LS, Grep, Glob tools instead
- **NEVER** create duplicate files (manager_v2.py, enhanced_xyz.py, utils_new.js) → ALWAYS extend existing files
- **NEVER** create multiple implementations of same concept → single source of truth
- **NEVER** copy-paste code blocks → extract into shared utilities/functions
- **NEVER** hardcode values that should be configurable → use config files/environment variables
- **NEVER** use naming like enhanced_, improved_, new_, v2_ → extend original files instead

### 📝 MANDATORY REQUIREMENTS
- **COMMIT** after every completed task/phase - no exceptions
- **GITHUB BACKUP** - Push to GitHub after every commit to maintain backup: `git push origin main`
- **USE TASK AGENTS** for all long-running operations (>30 seconds) - Bash commands stop when context switches
- **TODOWRITE** for complex tasks (3+ steps) → parallel agents → git checkpoints → test validation
- **READ FILES FIRST** before editing - Edit/Write tools will fail if you didn't read the file first
- **DEBT PREVENTION** - Before creating new files, check for existing similar functionality to extend
- **SINGLE SOURCE OF TRUTH** - One authoritative implementation per feature/concept

### ⚡ EXECUTION PATTERNS
- **PARALLEL TASK AGENTS** - Launch multiple Task agents simultaneously for maximum efficiency
- **SYSTEMATIC WORKFLOW** - TodoWrite → Parallel agents → Git checkpoints → GitHub backup → Test validation
- **GITHUB BACKUP WORKFLOW** - After every commit: `git push origin main` to maintain GitHub backup
- **BACKGROUND PROCESSING** - ONLY Task agents can run true background operations

### 🔍 MANDATORY PRE-TASK COMPLIANCE CHECK
> **STOP: Before starting any task, Claude Code must explicitly verify ALL points:**

**Step 1: Rule Acknowledgment**
- [ ] ✅ I acknowledge all critical rules in CLAUDE.md and will follow them

**Step 2: Task Analysis**
- [ ] Will this create files in root? → If YES, use proper module structure instead
- [ ] Will this take >30 seconds? → If YES, use Task agents not Bash
- [ ] Is this 3+ steps? → If YES, use TodoWrite breakdown first
- [ ] Am I about to use grep/find/cat? → If YES, use proper tools instead

**Step 3: Technical Debt Prevention (MANDATORY SEARCH FIRST)**
- [ ] **SEARCH FIRST**: Use Grep pattern="<functionality>.*<keyword>" to find existing implementations
- [ ] **CHECK EXISTING**: Read any found files to understand current functionality
- [ ] Does similar functionality already exist? → If YES, extend existing code
- [ ] Am I creating a duplicate class/manager? → If YES, consolidate instead
- [ ] Will this create multiple sources of truth? → If YES, redesign approach
- [ ] Have I searched for existing implementations? → Use Grep/Glob tools first
- [ ] Can I extend existing code instead of creating new? → Prefer extension over creation
- [ ] Am I about to copy-paste code? → Extract to shared utility instead

**Step 4: Session Management**
- [ ] Is this a long/complex task? → If YES, plan context checkpoints
- [ ] Have I been working >1 hour? → If YES, consider /compact or session break

> **⚠️ DO NOT PROCEED until all checkboxes are explicitly verified**

## 🐙 GITHUB SETUP & AUTO-BACKUP

### 📋 **GITHUB BACKUP WORKFLOW** (MANDATORY)
> **⚠️ CLAUDE CODE MUST FOLLOW THIS PATTERN:**

```bash
# After every commit, always run:
git push origin main

# This ensures:
# ✅ Remote backup of all changes
# ✅ Collaboration readiness
# ✅ Version history preservation
# ✅ Disaster recovery protection
```

### 🎯 **CLAUDE CODE GITHUB COMMANDS**
Essential GitHub operations for Claude Code:

```bash
# Check GitHub connection status
gh auth status && git remote -v

# Create new repository (if needed)
gh repo create [repo-name] --public --confirm

# Push changes (after every commit)
git push origin main

# Check repository status
gh repo view

# Clone repository (for new setup)
gh repo clone username/repo-name
```

## 🏗️ PROJECT OVERVIEW

### Laravel 8 智能客服系統架構

**技術棧 (Tech Stack):**
- **Framework**: Laravel 8
- **Frontend**: Livewire + Tailwind CSS
- **Database**: MySQL (RAG 資料儲存)
- **Logic**: JSON-based decision tree
- **AI/ML**: RAG (Retrieval-Augmented Generation)

**專案結構 (Project Structure):**
```
chatbot-ui3/
├── src/main/php/          # Laravel 應用程式碼
│   ├── Core/              # 核心邏輯 (聊天引擎、RAG處理)
│   ├── Utils/             # 工具函數
│   ├── Models/            # Laravel Models (資料表對應)
│   ├── Services/          # 服務層 (JSON邏輯解析、RAG查詢)
│   ├── Api/               # API端點 (Livewire組件)
│   ├── Training/          # AI模型訓練腳本
│   ├── Inference/         # AI推論引擎
│   └── Evaluation/        # 模型評估工具
├── data/                  # RAG資料管理
│   ├── raw/               # 原始客服知識庫
│   ├── processed/         # 處理後的向量資料
│   └── external/          # 外部數據源
├── models/                # AI模型存儲
│   ├── trained/           # 訓練好的模型
│   └── metadata/          # 模型配置
└── experiments/           # 實驗追蹤
    ├── configs/           # 實驗配置
    └── results/           # 實驗結果
```

### 🎯 **DEVELOPMENT STATUS**
- **Setup**: ✅ Initialized
- **Core Features**: 🚧 In Progress
- **Testing**: ⏳ Pending
- **Documentation**: ⏳ Pending

## 📋 LARAVEL 8 開發指南

### Laravel 專案結構映射
```
src/main/php/
├── Core/              → app/Core (核心業務邏輯)
├── Utils/             → app/Helpers (輔助函數)
├── Models/            → app/Models (Eloquent Models)
├── Services/          → app/Services (服務層)
├── Api/               → app/Http/Controllers 或 app/Http/Livewire
```

### Livewire 組件開發
- 所有 Livewire 組件放在 `src/main/php/Api/Livewire/`
- 視圖文件放在 `src/main/resources/views/livewire/`

### JSON 邏輯判斷架構
- JSON 配置文件放在 `src/main/resources/config/chatbot/`
- 解析引擎放在 `src/main/php/Services/JsonLogicParser.php`

### RAG 資料管理
- 原始知識庫: `data/raw/knowledge_base/`
- 向量化資料: `data/processed/vectors/`
- MySQL 表結構: `database/migrations/`

## 🎯 RULE COMPLIANCE CHECK

Before starting ANY task, verify:
- [ ] ✅ I acknowledge all critical rules above
- [ ] Files go in proper module structure (not root)
- [ ] Use Task agents for >30 second operations
- [ ] TodoWrite for 3+ step tasks
- [ ] Commit after each completed task
- [ ] Push to GitHub after every commit

## 🚀 COMMON COMMANDS

```bash
# Laravel 開發常用指令
php artisan serve                    # 啟動開發服務器
php artisan migrate                  # 執行資料庫遷移
php artisan make:model ModelName     # 創建 Model
php artisan make:livewire ComponentName  # 創建 Livewire 組件

# Git 工作流程
git add .
git commit -m "功能描述"
git push origin main                 # 必須執行以備份到 GitHub

# 測試指令
php artisan test                     # 執行測試
```

## 🚨 TECHNICAL DEBT PREVENTION

### ❌ WRONG APPROACH (Creates Technical Debt):
```bash
# Creating new file without searching first
Write(file_path="new_feature.php", content="...")
```

### ✅ CORRECT APPROACH (Prevents Technical Debt):
```bash
# 1. SEARCH FIRST
Grep(pattern="feature.*implementation", glob="*.php")
# 2. READ EXISTING FILES
Read(file_path="existing_feature.php")
# 3. EXTEND EXISTING FUNCTIONALITY
Edit(file_path="existing_feature.php", old_string="...", new_string="...")
```

## 🧹 DEBT PREVENTION WORKFLOW

### Before Creating ANY New File:
1. **🔍 Search First** - Use Grep/Glob to find existing implementations
2. **📋 Analyze Existing** - Read and understand current patterns
3. **🤔 Decision Tree**: Can extend existing? → DO IT | Must create new? → Document why
4. **✅ Follow Patterns** - Use established project patterns
5. **📈 Validate** - Ensure no duplication or technical debt

---

**⚠️ Prevention is better than consolidation - build clean from the start.**
**🎯 Focus on single source of truth and extending existing functionality.**
**📈 Each task should maintain clean architecture and prevent technical debt.**

---

<!-- Template by Chang Ho Chien | HC AI 說人話channel | v1.0.0 -->
<!-- Tutorial: https://youtu.be/8Q1bRZaHH24 -->
