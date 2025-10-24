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

- PHP >= 7.3 (PHP 7.4 recommended)
- Composer
- MySQL >= 5.7
- Node.js >= 14.x

### 🔧 Installation

```bash
# 1. Clone the repository
git clone https://github.com/Intelliturst/chatbot-ui3.git
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

## 📁 Project Structure

```
chatbot-ui3/
├── app/                   # Laravel application code
│   ├── Http/
│   │   ├── Controllers/   # HTTP controllers
│   │   └── Livewire/      # Livewire components for chatbot UI
│   ├── Models/            # Eloquent models (RAG data, chat history)
│   └── Services/          # Business logic services
├── config/                # Laravel configuration files
│   └── chatbot.php        # Chatbot-specific configuration
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── views/
│   │   └── livewire/      # Livewire component views
│   ├── css/               # Tailwind CSS source
│   └── js/                # JavaScript assets
├── routes/
│   └── web.php            # Web routes
├── public/                # Public assets
├── storage/               # Laravel storage
├── tests/                 # PHPUnit tests
├── data/                  # AI/ML data management
│   ├── raw/               # Original knowledge base
│   ├── processed/         # Vectorized data
│   └── external/          # External data sources
├── models/                # AI model storage
│   ├── trained/           # Trained models
│   └── metadata/          # Model configurations
├── notebooks/             # Jupyter notebooks for analysis
│   ├── exploratory/       # Data exploration
│   └── experiments/       # ML experiments
└── experiments/           # ML experiment tracking
```

## 📝 Development Guidelines

**⚠️ IMPORTANT: Read CLAUDE.md first!**

### Critical Rules:
1. **Always search first** before creating new files
2. **Extend existing** functionality rather than duplicating
3. **Use Task agents** for operations >30 seconds
4. **Commit after each feature** and push to GitHub
5. **Never create files in root** - use proper module structure

### 🔄 Development Workflow

```bash
# 1. Create feature branch
git checkout -b feature/your-feature-name

# 2. Implement feature following Laravel conventions
# 3. Write tests
php artisan test

# 4. Commit changes
git add .
git commit -m "Add: feature description"

# 5. MANDATORY: Push to GitHub for backup
git push origin feature/your-feature-name
```

## 📚 技術架構 (Technical Architecture)

### Laravel 8 Components

- **Livewire**: Real-time chatbot UI components
- **Eloquent ORM**: MySQL database management for RAG data
- **Blade Templates**: View rendering with Tailwind CSS
- **Migrations**: Database version control
- **Seeders**: Sample data for development

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

- **Development Guide**: See `CLAUDE.md`
- **Laravel Docs**: https://laravel.com/docs/8.x
- **Livewire Docs**: https://laravel-livewire.com/docs

## 🤝 Contributing

1. Follow CLAUDE.md rules strictly
2. Search for existing functionality before creating new files
3. Write tests for all features
4. Commit frequently and push to GitHub
5. Use descriptive commit messages

## 📝 License

[MIT License](LICENSE)

## 🙏 Credits

**Template by**: Chang Ho Chien | HC AI 說人話channel | v1.0.0
**Tutorial**: https://youtu.be/8Q1bRZaHH24

---

**⚠️ Important**: Always read and follow `CLAUDE.md` rules before starting any task!
