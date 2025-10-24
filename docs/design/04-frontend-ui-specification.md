# 虹宇職訓智能客服系統 - 前端UI設計規範

**文件版本**: 1.0
**最後更新**: 2025-10-24
**作者**: 虹宇職訓開發團隊

---

## 📋 目錄

1. [設計理念](#設計理念)
2. [品牌識別系統](#品牌識別系統)
3. [彈出式視窗設計](#彈出式視窗設計)
4. [組件設計規範](#組件設計規範)
5. [RWD響應式設計](#rwd響應式設計)
6. [動畫效果](#動畫效果)
7. [Tailwind CSS類別參考](#tailwind-css類別參考)

---

## 設計理念

### 設計原則

1. **簡潔專業**：參考富邦銀行UI，使用商務風格，白底配色
2. **親切友善**：使用表情符號、圓角設計增加親切感
3. **高效互動**：提供快速選項按鈕，減少用戶輸入
4. **清晰層次**：品牌區/內容區/互動區分離明確

### 參考設計 - 富邦銀行智能客服

**優點學習**：
- ✅ 視覺層次清晰（頂部品牌 / 中間內容 / 底部互動）
- ✅ 卡片式設計提升可讀性
- ✅ 快速選項按鈕（常見問題）

---

## 品牌識別系統

### 虹宇品牌色系

```css
/* 主色調 */
--primary: #4F46E5;      /* 虹宇紫藍色 */
--primary-dark: #4338CA;  /* 深紫藍（hover）*/
--primary-light: #818CF8; /* 淺紫藍（背景）*/

/* 輔助色 */
--secondary: #10B981;     /* 綠色（成功）*/
--accent: #F59E0B;        /* 橙色（警告/強調）*/
--danger: #EF4444;        /* 紅色（錯誤）*/

/* 中性色 */
--gray-50: #F9FAFB;
--gray-100: #F3F4F6;
--gray-200: #E5E7EB;
--gray-500: #6B7280;
--gray-700: #374151;
--gray-900: #111827;

/* 文字色 */
--text-primary: #111827;
--text-secondary: #6B7280;
--text-inverse: #FFFFFF;
```

### Logo使用規範

```html
<!-- Logo 位置：頂部左側 -->
<img src="/public/logo.png" alt="虹宇職訓" class="h-8 w-auto">
```

**尺寸規範**：
- Desktop: 高度32px（h-8）
- Mobile: 高度24px（h-6）

---

## 彈出式視窗設計

### 視窗結構

```
┌────────────────────────────┐
│  [Logo] 虹宇職訓     [ℹ️] [✕] │ ← 頂部欄
├────────────────────────────┤
│                            │
│   [對話內容區]              │ ← 訊息顯示區
│   - 訊息氣泡                │
│   - 時間戳                  │
│   - 課程卡片                │
│   - 快速選項按鈕            │
│                            │
├────────────────────────────┤
│ [快速問題按鈕群]            │ ← 互動區
│ [輸入框]            [發送]  │
└────────────────────────────┘
```

### 尺寸規範

#### Desktop（>=768px）

```css
.chatbot-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 400px;
  max-height: 600px;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
              0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
```

#### Mobile（<768px）

```css
.chatbot-widget-mobile {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100vh;
  border-radius: 0;
}
```

### 浮動按鈕

**未展開狀態**：

```html
<button class="fixed bottom-6 right-6 w-16 h-16 bg-primary rounded-full shadow-lg
               hover:bg-primary-dark transition-all duration-300 flex items-center justify-center">
  <svg class="w-8 h-8 text-white">
    <!-- 聊天圖示 -->
  </svg>
</button>
```

**展開狀態**：
- 按鈕隱藏
- 視窗滑入動畫（從右下角）

---

## 組件設計規範

### 1. 頂部欄（Header）

```html
<div class="flex items-center justify-between p-4 bg-primary text-white rounded-t-2xl">
  <!-- Logo + 品牌名稱 -->
  <div class="flex items-center space-x-3">
    <img src="/public/logo.png" alt="虹宇職訓" class="h-8 w-auto">
    <h2 class="text-lg font-semibold">虹宇職訓</h2>
  </div>

  <!-- 功能按鈕 -->
  <div class="flex space-x-2">
    <!-- 資訊按鈕 -->
    <button class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors">
      <svg class="w-5 h-5"><!-- info icon --></svg>
    </button>
    <!-- 關閉按鈕 -->
    <button class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors"
            wire:click="toggleWidget">
      <svg class="w-5 h-5"><!-- close icon --></svg>
    </button>
  </div>
</div>
```

### 2. 訊息氣泡（Message Bubble）

#### 用戶訊息（右側）

```html
<div class="flex justify-end mb-4">
  <div class="bg-gray-100 text-gray-900 px-4 py-3 rounded-2xl rounded-tr-sm max-w-[80%]">
    <p class="text-sm">{{ message }}</p>
    <span class="text-xs text-gray-500 mt-1 block">{{ timestamp }}</span>
  </div>
</div>
```

#### AI訊息（左側）

```html
<div class="flex justify-start mb-4">
  <!-- AI頭像 -->
  <div class="w-8 h-8 rounded-full flex-shrink-0 mr-2">
    <img src="/agent.png" alt="AI助手" class="w-full h-full">
  </div>

  <div class="bg-primary/10 text-gray-900 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[80%]">
    <p class="text-sm whitespace-pre-line">{{ message }}</p>
    <span class="text-xs text-gray-500 mt-1 block">{{ timestamp }}</span>
  </div>
</div>
```

### 3. 課程卡片（Course Card）

**簡化版（清單模式）**：

```html
<div class="bg-white border-2 border-gray-200 rounded-xl p-4 mb-3 hover:border-primary
            transition-colors cursor-pointer">
  <!-- 課程編號徽章 -->
  <span class="inline-block bg-primary text-white text-xs px-2 py-1 rounded-full mb-2">
    1號課程
  </span>

  <h3 class="text-base font-semibold text-gray-900 mb-2">
    AI生成影音行銷與直播技巧實戰班
  </h3>

  <div class="space-y-1 text-sm text-gray-600">
    <p>📅 開課：2025/11/28</p>
    <p>⏰ 報名截止：2025/11/18 17:30</p>
    <p>💰 政府補助100%</p>
    <p>📍 桃園中壢</p>
  </div>

  <div class="mt-3 flex space-x-2">
    <button class="flex-1 bg-primary/10 text-primary text-sm py-2 rounded-lg
                   hover:bg-primary hover:text-white transition-colors">
      查看詳情
    </button>
    <button class="flex-1 bg-primary text-white text-sm py-2 rounded-lg
                   hover:bg-primary-dark transition-colors">
      我要報名
    </button>
  </div>
</div>
```

### 4. 快速選項按鈕（Quick Options）

**水平滾動按鈕群**：

```html
<div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
  <button class="inline-flex items-center px-4 py-2 bg-white border-2 border-primary
                 text-primary rounded-full text-sm font-medium hover:bg-primary
                 hover:text-white transition-colors whitespace-nowrap"
          wire:click="selectOption('option1')">
    <span class="mr-1">📚</span>
    課程查詢
  </button>

  <button class="inline-flex items-center px-4 py-2 bg-white border-2 border-primary
                 text-primary rounded-full text-sm font-medium hover:bg-primary
                 hover:text-white transition-colors whitespace-nowrap"
          wire:click="selectOption('option2')">
    <span class="mr-1">💰</span>
    補助諮詢
  </button>

  <!-- 更多按鈕... -->
</div>
```

### 5. 關聯問題按鈕（Related Questions）

```html
<div class="space-y-2 mt-3">
  <p class="text-xs text-gray-500">想了解更多？點擊下方問題：</p>

  <button class="w-full text-left bg-gray-50 hover:bg-gray-100 px-3 py-2
                 rounded-lg text-sm text-gray-700 transition-colors"
          wire:click="askRelatedQuestion('報名截止時間')">
    📅 報名截止時間
  </button>

  <button class="w-full text-left bg-gray-50 hover:bg-gray-100 px-3 py-2
                 rounded-lg text-sm text-gray-700 transition-colors"
          wire:click="askRelatedQuestion('上課地點')">
    📍 上課地點
  </button>

  <button class="w-full text-left bg-gray-50 hover:bg-gray-100 px-3 py-2
                 rounded-lg text-sm text-gray-700 transition-colors"
          wire:click="askRelatedQuestion('課程費用')">
    💰 課程費用
  </button>
</div>
```

### 6. 輸入區域（Input Area）

```html
<div class="p-4 bg-white border-t border-gray-200 rounded-b-2xl">
  <!-- 快速選項按鈕群 -->
  <div class="mb-3" x-show="showQuickOptions">
    <!-- 快速選項按鈕 -->
  </div>

  <!-- 輸入框 -->
  <div class="flex items-end space-x-2">
    <div class="flex-1">
      <textarea
        wire:model.defer="userInput"
        rows="1"
        placeholder="請輸入您的問題..."
        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
               focus:outline-none focus:border-primary resize-none text-sm"
        x-data="{ resize: () => { $el.style.height = '40px'; $el.style.height = $el.scrollHeight + 'px' } }"
        x-on:input="resize()"
      ></textarea>
    </div>

    <!-- 發送按鈕 -->
    <button
      wire:click="sendMessage"
      class="w-12 h-12 bg-primary text-white rounded-xl hover:bg-primary-dark
             transition-colors flex items-center justify-center flex-shrink-0"
      :disabled="$wire.userInput.trim() === ''">
      <svg class="w-5 h-5"><!-- send icon --></svg>
    </button>
  </div>
</div>
```

### 7. 載入動畫（Loading）

**打字效果**：

```html
<div class="flex justify-start mb-4">
  <div class="bg-primary/10 px-4 py-3 rounded-2xl rounded-tl-sm">
    <div class="flex space-x-2">
      <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
      <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
      <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
    </div>
  </div>
</div>
```

---

## RWD響應式設計

### 斷點定義

```css
/* Tailwind 預設斷點 */
sm: 640px
md: 768px
lg: 1024px
xl: 1280px
```

### Desktop（>=768px）

- 固定右下角
- 寬度：400px
- 最大高度：600px
- 圓角：16px

### Tablet（>=640px and <768px）

- 固定右下角
- 寬度：350px
- 最大高度：500px

### Mobile（<640px）

- 全螢幕展開
- 寬度：100%
- 高度：100vh
- 圓角：0
- 頂部加入"返回"按鈕

### 響應式類別範例

```html
<div class="
  fixed
  bottom-0 md:bottom-6
  right-0 md:right-6
  w-full md:w-[400px]
  h-screen md:h-auto md:max-h-[600px]
  rounded-none md:rounded-2xl
">
  <!-- 內容 -->
</div>
```

---

## 動畫效果

### 1. 視窗展開/收合

```css
/* Tailwind Animation */
@keyframes slideInUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-in-up {
  animation: slideInUp 0.3s ease-out;
}

/* Desktop：從右下角滑入 */
@keyframes slideInRight {
  from {
    transform: translate(100%, 100%);
    opacity: 0;
  }
  to {
    transform: translate(0, 0);
    opacity: 1;
  }
}

.animate-slide-in-right {
  animation: slideInRight 0.3s ease-out;
}
```

### 2. 訊息逐條顯示

```html
<div
  x-show="visible"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 translate-y-2"
  x-transition:enter-end="opacity-100 translate-y-0"
>
  <!-- 訊息內容 -->
</div>
```

### 3. 按鈕hover效果

```html
<button class="
  transition-all duration-200
  hover:scale-105
  hover:shadow-lg
  active:scale-95
">
  按鈕
</button>
```

---

## Tailwind CSS類別參考

### 完整視窗範例

```html
<div
  x-data="{ open: @entangle('isOpen') }"
  x-show="open"
  class="fixed bottom-0 right-0 md:bottom-6 md:right-6 w-full md:w-[400px]
         h-screen md:h-auto md:max-h-[600px] bg-white rounded-none md:rounded-2xl
         shadow-2xl flex flex-col overflow-hidden z-50"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:translate-x-full"
  x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
>
  <!-- Header -->
  <div class="flex items-center justify-between p-4 bg-primary text-white
              rounded-t-none md:rounded-t-2xl flex-shrink-0">
    <div class="flex items-center space-x-3">
      <img src="/public/logo.png" alt="虹宇職訓" class="h-6 md:h-8 w-auto">
      <h2 class="text-base md:text-lg font-semibold">虹宇職訓</h2>
    </div>

    <div class="flex space-x-2">
      <button class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors">
        <svg class="w-5 h-5 mx-auto"><!-- info --></svg>
      </button>
      <button
        wire:click="toggleWidget"
        class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors">
        <svg class="w-5 h-5 mx-auto"><!-- close --></svg>
      </button>
    </div>
  </div>

  <!-- Messages Area -->
  <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
    @foreach($messages as $message)
      @if($message['role'] === 'user')
        <!-- 用戶訊息 -->
        <div class="flex justify-end">
          <div class="bg-gray-100 text-gray-900 px-4 py-3 rounded-2xl rounded-tr-sm max-w-[80%]">
            <p class="text-sm">{{ $message['content'] }}</p>
            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
          </div>
        </div>
      @else
        <!-- AI訊息 -->
        <div class="flex justify-start">
          <div class="bg-primary/10 text-gray-900 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[80%]">
            <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>

            <!-- 關聯問題 -->
            @if(!empty($message['quick_options']))
              <div class="mt-3 space-y-2">
                @foreach($message['quick_options'] as $option)
                  <button
                    wire:click="selectOption('{{ $option }}')"
                    class="w-full text-left bg-white hover:bg-gray-50 px-3 py-2
                           rounded-lg text-sm text-gray-700 transition-colors border border-gray-200">
                    {{ $option }}
                  </button>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @endif
    @endforeach

    <!-- Loading -->
    @if($isLoading)
      <div class="flex justify-start">
        <div class="bg-primary/10 px-4 py-3 rounded-2xl rounded-tl-sm">
          <div class="flex space-x-2">
            <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
          </div>
        </div>
      </div>
    @endif
  </div>

  <!-- Input Area -->
  <div class="p-4 bg-white border-t border-gray-200 rounded-b-none md:rounded-b-2xl flex-shrink-0">
    <div class="flex items-end space-x-2">
      <textarea
        wire:model.defer="userInput"
        rows="1"
        placeholder="請輸入您的問題..."
        class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
               focus:outline-none focus:border-primary resize-none text-sm"
        wire:keydown.enter.prevent="sendMessage"
      ></textarea>

      <button
        wire:click="sendMessage"
        class="w-12 h-12 bg-primary text-white rounded-xl hover:bg-primary-dark
               transition-colors flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
      </button>
    </div>
  </div>
</div>
```

---

## 附錄

### 相關文件

- [01-system-architecture.md](./01-system-architecture.md) - 系統架構設計
- [03-agent-implementation.md](./03-agent-implementation.md) - 代理實現規範
- [06-laravel-development-guide.md](./06-laravel-development-guide.md) - Laravel開發規範

### 設計資源

- Tailwind CSS: https://tailwindcss.com
- Alpine.js: https://alpinejs.dev
- Livewire: https://laravel-livewire.com

---

**文件結束**
