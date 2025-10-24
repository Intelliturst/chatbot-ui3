# chatbot-ui3

> **Laravel 8 智能客服系統 | Intelligent Customer Service Interface**

使用Laravel8來開發的智能客服介面，用json來建構基礎邏輯判斷，用mysql資料表來儲存RAG資料,用livewire來開發Ajax互動效果,用Tailwind來開發CSS樣式，目標是做出能夠嵌在網頁右下角的彈出式智能客服視窗，回答用戶的問題。

## ✨ 功能特點 (Features)

- 🤖 **智能對話引擎**: 基於 JSON 邏輯決策樹的智能回答系統
- 📚 **RAG 技術**: 使用 MySQL 存儲檢索增強生成數據
- ⚡ **即時互動**: Livewire 驅動的 Ajax 無刷新體驗
- 🎨 **現代化界面**: Tailwind CSS 打造的美觀UI
- 💬 **彈出式視窗**: 可嵌入網頁右下角的客服視窗
- 📊 **AI/ML 整合**: 完整的機器學習工作流支持

## 🚀 Quick Start

### 📋 Prerequisites

1. **Read CLAUDE.md first** - Contains essential rules for Claude Code
2. PHP >= 7.4
3. Composer
4. MySQL >= 5.7
5. Node.js >= 14.x

### 🔧 Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd chatbot-ui3

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database in .env file
# DB_DATABASE=chatbot_ui3
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# 7. Run migrations
php artisan migrate

# 8. Compile assets
npm run dev

# 9. Start development server
php artisan serve
```

## 📁 AI/ML Project Structure

This project follows an enterprise-grade AI/ML structure:

```
chatbot-ui3/
├── CLAUDE.md              # Essential rules for Claude Code
├── README.md              # Project documentation
├── src/                   # Source code (NEVER put files in root)
│   ├── main/
│   │   ├── php/           # Laravel application code
│   │   │   ├── Core/      # Core chatbot logic & RAG processing
│   │   │   ├── Utils/     # Helper functions
│   │   │   ├── Models/    # Eloquent models
│   │   │   ├── Services/  # JSON logic parser, RAG queries
│   │   │   ├── Api/       # Livewire components & API endpoints
│   │   │   ├── Training/  # AI model training scripts
│   │   │   ├── Inference/ # AI inference engine
│   │   │   └── Evaluation/# Model evaluation tools
│   │   └── resources/
│   │       ├── config/    # Chatbot JSON configs
│   │       ├── data/      # Sample data
│   │       └── assets/    # Tailwind CSS, images
│   └── test/
│       ├── unit/          # Unit tests
│       └── integration/   # Integration tests
├── data/                  # RAG data management
│   ├── raw/               # Original knowledge base
│   ├── processed/         # Vectorized data
│   └── external/          # External data sources
├── notebooks/             # Jupyter notebooks for analysis
├── models/                # AI model storage
│   ├── trained/           # Trained models
│   └── metadata/          # Model configs
├── experiments/           # ML experiment tracking
├── docs/                  # Documentation
├── output/                # Generated files
└── logs/                  # Log files
```

## 🎯 Development Guidelines

**Critical Rules (See CLAUDE.md for details):**

1. **Always search first** before creating new files
2. **Extend existing** functionality rather than duplicating
3. **Use Task agents** for operations >30 seconds
4. **Single source of truth** for all functionality
5. **Commit after each feature** and push to GitHub
6. **Never create files in root** - use proper module structure

### 🔄 Development Workflow

```bash
# 1. Start with CLAUDE.md compliance check
# 2. Implement feature in proper module (src/main/php/...)
# 3. Write tests in src/test/
# 4. Commit changes
git add .
git commit -m "描述功能"

# 5. MANDATORY: Push to GitHub for backup
git push origin main

# 6. Verify tests pass
php artisan test
```

## 📚 技術架構 (Technical Architecture)

### Laravel 8 Components

- **Livewire**: Real-time chatbot UI components
- **Eloquent ORM**: MySQL database management for RAG data
- **Blade Templates**: View rendering with Tailwind CSS

### JSON Logic Engine

JSON configuration files define conversation flows:

```json
{
  "intent": "greeting",
  "patterns": ["你好", "hi", "hello"],
  "responses": ["您好！我是智能客服，有什麼可以幫助您的嗎？"],
  "next_actions": ["show_menu"]
}
```

### RAG (Retrieval-Augmented Generation)

- Knowledge base stored in MySQL tables
- Vector embeddings for semantic search
- Context-aware response generation

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ChatbotTest

# Run with coverage
php artisan test --coverage
```

## 📊 AI/ML Workflow

### Training Pipeline

```bash
# 1. Prepare data
php artisan chatbot:prepare-data

# 2. Train model
php artisan chatbot:train

# 3. Evaluate
php artisan chatbot:evaluate

# 4. Deploy
php artisan chatbot:deploy
```

## 🛠️ Common Commands

```bash
# Laravel development
php artisan serve                    # Start dev server
php artisan migrate                  # Run migrations
php artisan make:model ModelName     # Create model
php artisan make:livewire ComponentName  # Create Livewire component

# Asset compilation
npm run dev                          # Development build
npm run watch                        # Watch for changes
npm run production                   # Production build

# Git workflow
git add .
git commit -m "Feature description"
git push origin main                 # MANDATORY: Backup to GitHub
```

## 📖 Documentation

- **Development Guide**: See `docs/dev/`
- **API Documentation**: See `docs/api/`
- **User Manual**: See `docs/user/`

## 🤝 Contributing

1. Follow CLAUDE.md rules strictly
2. Search for existing functionality before creating new files
3. Write tests for all features
4. Commit frequently and push to GitHub
5. Use descriptive commit messages

## 📝 License

[Your License Here]

## 🙏 Credits

**Template by**: Chang Ho Chien | HC AI 說人話channel | v1.0.0
**Tutorial**: https://youtu.be/8Q1bRZaHH24

---

**⚠️ Important**: Always read and follow `CLAUDE.md` rules before starting any task!
