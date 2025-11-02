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

### 互動狀態管理（Loading States）

#### 狀態定義

**正常狀態**（isLoading = false）：
- ✅ 輸入框可用
- ✅ 發送按鈕可用
- ✅ 快速選項按鈕可用
- ✅ 所有互動元素正常顯示

**載入狀態**（isLoading = true）：
- 🔒 輸入框禁用（disabled + 灰底）
- 🔒 發送按鈕禁用（disabled + 半透明）
- 🔒 所有快速選項按鈕禁用（disabled + 半透明）
- ⏳ 顯示 AI 思考動畫（對話泡泡 + 三個跳動點）
- ❌ 防止任何新的訊息發送

#### Livewire 實現

所有互動元素添加 `wire:loading.attr="disabled"`：

```html
<!-- 輸入框 -->
<textarea
  wire:model.defer="userInput"
  wire:loading.attr="disabled"
  class="... disabled:bg-gray-100 disabled:cursor-not-allowed"
></textarea>

<!-- 發送按鈕 -->
<button
  wire:click="sendMessage"
  wire:loading.attr="disabled"
  class="... disabled:opacity-50 disabled:cursor-not-allowed"
>發送</button>

<!-- 快速選項按鈕 -->
<button
  wire:click="selectOption('...')"
  wire:loading.attr="disabled"
  class="... disabled:opacity-50 disabled:cursor-not-allowed"
>選項</button>
```

#### 視覺反饋規範

| 元素 | 正常狀態 | 禁用狀態 |
|------|---------|---------|
| 輸入框 | 白底/灰底 | 灰底 + 不可輸入 |
| 發送按鈕 | 紫底白字 + 陰影 | 50%透明 + 禁止點擊 |
| 選項按鈕 | 紫底白字 + hover效果 | 50%透明 + 禁止點擊 |
| 思考動畫 | 隱藏 | 顯示（對話泡泡+跳動點）|

---

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
    <p>⏰ 報名截止：2025/11/18 17:00</p>
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

**水平滾動按鈕群（更新：紫底白字設計）**：

```html
<div class="flex flex-wrap gap-2">
  <button class="group inline-flex items-center px-4 py-2
                 bg-primary text-white rounded-xl text-sm font-medium
                 hover:bg-primary-dark hover:shadow-lg
                 transition-all duration-300
                 transform hover:scale-105 active:scale-95
                 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                 animate-slide-in-up"
          wire:click="selectOption('課程查詢')"
          wire:loading.attr="disabled"
          style="animation-delay: 0s">
    <svg class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100 transition-opacity"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5l7 7-7 7"></path>
    </svg>
    課程查詢
  </button>

  <button class="group inline-flex items-center px-4 py-2
                 bg-primary text-white rounded-xl text-sm font-medium
                 hover:bg-primary-dark hover:shadow-lg
                 transition-all duration-300
                 transform hover:scale-105 active:scale-95
                 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                 animate-slide-in-up"
          wire:click="selectOption('補助諮詢')"
          wire:loading.attr="disabled"
          style="animation-delay: 0.1s">
    <svg class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100 transition-opacity"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5l7 7-7 7"></path>
    </svg>
    補助諮詢
  </button>

  <!-- 更多按鈕... -->
</div>
```

**配色規範**：
- **預設狀態**: 紫藍底 (#4F46E5) + 白字
- **Hover 狀態**: 深紫藍底 (#4338CA) + 陰影放大效果
- **Active 狀態**: 按下縮小 (scale-95)
- **Disabled 狀態**: 50% 透明 + 禁用游標 + 移除動畫

**圖示規範**：
- 使用右箭頭圖示 (→)
- 預設 80% 透明度
- Hover 時 100% 透明度
- 圖示大小: 16px (w-4 h-4)

#### 顯示邏輯規範

**核心原則**: 只顯示最後一條 AI 訊息的快速選項按鈕

**設計理由**:
- ✅ 避免介面混亂（多組按鈕堆疊）
- ✅ 引導用戶關注最新回覆
- ✅ 符合對話流程邏輯
- ✅ 提升視覺清晰度

**Blade 實現**:

```blade
@foreach($messages as $index => $message)
  @if($message['role'] === 'assistant')
    <div class="flex justify-start">
      <!-- AI 頭像 -->
      <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                  bg-gradient-to-br from-primary to-primary-dark p-0.5">
        <img src="/agent.png" alt="AI助手"
             class="w-full h-full rounded-full bg-white p-0.5">
      </div>

      <!-- 訊息內容 -->
      <div class="flex-1 max-w-[80%]">
        <div class="bg-white text-gray-900 px-4 py-3 rounded-2xl rounded-tl-md
                    shadow-md border border-gray-100">
          <p class="text-sm">{{ $message['content'] }}</p>
          <span class="text-xs text-gray-400 mt-2 block">
            {{ $message['timestamp'] }}
          </span>
        </div>

        <!-- 快速選項：僅最後一條訊息顯示 -->
        @if($index === count($messages) - 1 && !empty($message['quick_options']) && !$isLoading)
          <div class="mt-3 flex flex-wrap gap-2">
            @foreach($message['quick_options'] as $optionIndex => $option)
              <button
                wire:click="selectOption('{{ $option }}')"
                wire:loading.attr="disabled"
                class="group inline-flex items-center px-4 py-2
                       bg-primary text-white rounded-xl text-sm font-medium
                       hover:bg-primary-dark hover:shadow-lg
                       transition-all duration-300
                       transform hover:scale-105 active:scale-95
                       disabled:opacity-50 disabled:cursor-not-allowed
                       animate-slide-in-up"
                style="animation-delay: {{ $optionIndex * 0.1 }}s">
                <svg class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7"></path>
                </svg>
                {{ $option }}
              </button>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  @endif
@endforeach
```

**條件檢查說明**:
1. `$index === count($messages) - 1` → 檢查是否為最後一條訊息
2. `!empty($message['quick_options'])` → 檢查是否有快速選項
3. `!$isLoading` → 檢查不在載入狀態（載入時隱藏所有按鈕）

**動畫效果**:
- 每個按鈕依序淡入（延遲 0.1s 遞增）
- 產生流暢的出現效果
- 使用 `animate-slide-in-up` 類別

---

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

**AI 思考動畫（對話泡泡設計）**：

```html
<!-- AI 思考動畫 -->
@if($isLoading)
  <div class="flex justify-start animate-fade-in">
    <!-- AI 頭像 -->
    <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                bg-gradient-to-br from-primary to-primary-dark p-0.5">
      <img src="/agent.png" alt="AI助手"
           class="w-full h-full rounded-full bg-white p-0.5">
    </div>

    <!-- 思考動畫泡泡（無文字） -->
    <div class="bg-white px-5 py-4 rounded-2xl rounded-tl-md
                shadow-md border border-gray-100">
      <div class="flex space-x-2">
        <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"></div>
        <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
             style="animation-delay: 0.1s"></div>
        <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
             style="animation-delay: 0.2s"></div>
      </div>
    </div>
  </div>
@endif
```

**設計特點**：
- ✅ 對話泡泡樣式與 AI 訊息完全一致
- ✅ 包含 AI 頭像（讓用戶知道是 AI 在思考）
- ✅ 只有三個跳動點，無文字（簡潔明確）
- ✅ 左上角切角（rounded-tl-md）保持一致性
- ✅ 淡入動畫（animate-fade-in）讓出現更自然

**尺寸規範**：
- 跳動點大小：2.5px × 2.5px（w-2.5 h-2.5）
- 點間距：0.5rem（space-x-2）
- 泡泡內邊距：1.25rem × 1rem（px-5 py-4）
- 動畫延遲：0.1s, 0.2s（產生波浪效果）

**動畫說明**：
```css
/* 淡入動畫 */
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}

/* 跳動動畫（Tailwind 內建） */
.animate-bounce {
  animation: bounce 1s infinite;
}
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

## 禁用狀態視覺規範

### 設計原則

所有禁用狀態的元素應該：
1. 視覺上明顯表示不可用
2. 防止用戶點擊（cursor-not-allowed）
3. 保持與正常狀態的一致性
4. 提供足夠的視覺反饋

### 禁用狀態樣式

#### 1. 按鈕禁用

**快速選項按鈕**：

```html
<button class="
  bg-primary text-white rounded-xl
  hover:bg-primary-dark hover:shadow-lg
  transition-all duration-300
  transform hover:scale-105 active:scale-95
  disabled:opacity-50
  disabled:cursor-not-allowed
  disabled:transform-none
  disabled:shadow-none
"
wire:loading.attr="disabled">
  按鈕文字
</button>
```

**效果說明**：
- `disabled:opacity-50` → 50% 透明度（視覺反饋）
- `disabled:cursor-not-allowed` → 禁用游標圖示
- `disabled:transform-none` → 移除 hover 縮放效果
- `disabled:shadow-none` → 移除陰影效果

**發送按鈕**：

```html
<button class="
  w-12 h-12 bg-primary text-white rounded-xl
  hover:bg-primary-dark hover:rotate-12
  transition-all duration-300
  disabled:opacity-50
  disabled:cursor-not-allowed
  disabled:rotate-0
"
wire:click="sendMessage"
wire:loading.attr="disabled">
  <svg><!-- send icon --></svg>
</button>
```

#### 2. 輸入框禁用

```html
<textarea class="
  flex-1 px-4 py-3
  bg-gray-50 border-2 border-gray-200 rounded-xl
  focus:outline-none focus:border-primary
  resize-none text-sm
  disabled:bg-gray-100
  disabled:cursor-not-allowed
  disabled:text-gray-500
  disabled:border-gray-200
"
wire:model.defer="userInput"
wire:loading.attr="disabled">
</textarea>
```

**效果說明**：
- `disabled:bg-gray-100` → 背景變深灰色
- `disabled:cursor-not-allowed` → 禁用游標圖示
- `disabled:text-gray-500` → 文字變灰色
- `disabled:border-gray-200` → 邊框保持灰色（不變色）

#### 3. 視覺狀態對比

| 元素 | 正常狀態 | Hover 狀態 | 禁用狀態 |
|------|---------|-----------|----------|
| 快速選項按鈕 | 紫底白字 | 深紫底 + 陰影 + 放大 | 50%透明 + 禁止游標 |
| 發送按鈕 | 紫底白字 | 深紫底 + 旋轉 | 50%透明 + 禁止游標 |
| 輸入框 | 灰底黑字 | 紫邊框 | 深灰底 + 灰字 + 禁止游標 |

### Tailwind 配置

確保 `tailwind.config.js` 包含 disabled 變體：

```javascript
module.exports = {
  theme: {
    // ... 其他配置
  },
  variants: {
    extend: {
      opacity: ['disabled'],
      cursor: ['disabled'],
      backgroundColor: ['disabled'],
      textColor: ['disabled'],
      borderColor: ['disabled'],
      transform: ['disabled'],
      boxShadow: ['disabled'],
    }
  }
}
```

### 使用範例

**完整按鈕範例（包含所有狀態）**：

```html
<button
  wire:click="selectOption('課程查詢')"
  wire:loading.attr="disabled"
  class="
    /* 基礎樣式 */
    inline-flex items-center px-4 py-2
    bg-primary text-white rounded-xl text-sm font-medium

    /* Hover 狀態 */
    hover:bg-primary-dark hover:shadow-lg
    hover:scale-105

    /* Active 狀態 */
    active:scale-95

    /* 禁用狀態 */
    disabled:opacity-50
    disabled:cursor-not-allowed
    disabled:transform-none
    disabled:shadow-none

    /* 動畫 */
    transition-all duration-300
  "
>
  課程查詢
</button>
```

---

## Tailwind CSS類別參考

### 完整視窗範例（整合所有優化）

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
      <img src="/logo.png" alt="虹宇職訓" class="h-6 md:h-8 w-auto">
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
  <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-gray-50 to-white">
    @foreach($messages as $index => $message)
      @if($message['role'] === 'user')
        <!-- 用戶訊息 -->
        <div class="flex justify-end animate-slide-in-right"
             style="animation-delay: {{ $index * 0.05 }}s">
          <div class="bg-gradient-to-br from-gray-100 to-gray-200 text-gray-900
                      px-4 py-3 rounded-2xl rounded-tr-md max-w-[80%]
                      shadow-sm hover:shadow-md transition-shadow">
            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $message['content'] }}</p>
            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
          </div>
        </div>
      @else
        <!-- AI訊息 -->
        <div class="flex justify-start animate-slide-in-left"
             style="animation-delay: {{ $index * 0.05 }}s">
          <!-- AI 頭像 -->
          <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                      bg-gradient-to-br from-primary to-primary-dark p-0.5">
            <img src="/agent.png" alt="AI助手"
                 class="w-full h-full rounded-full bg-white p-0.5">
          </div>

          <!-- 訊息內容 -->
          <div class="flex-1 max-w-[80%]">
            <div class="bg-white text-gray-900 px-4 py-3 rounded-2xl rounded-tl-md
                        shadow-md border border-gray-100 hover:shadow-lg transition-shadow">
              <p class="text-sm leading-relaxed whitespace-pre-line">{{ $message['content'] }}</p>
              <span class="text-xs text-gray-400 mt-2 block">{{ $message['timestamp'] }}</span>
            </div>

            <!-- 快速選項按鈕（僅最後一條訊息顯示） -->
            @if($index === count($messages) - 1 && !empty($message['quick_options']) && !$isLoading)
              <div class="mt-3 flex flex-wrap gap-2">
                @foreach($message['quick_options'] as $optionIndex => $option)
                  <button
                    wire:click="selectOption('{{ $option }}')"
                    wire:loading.attr="disabled"
                    class="group inline-flex items-center px-4 py-2
                           bg-primary text-white rounded-xl text-sm font-medium
                           hover:bg-primary-dark hover:shadow-lg
                           transition-all duration-300
                           transform hover:scale-105 active:scale-95
                           disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                           animate-slide-in-up"
                    style="animation-delay: {{ $optionIndex * 0.1 }}s">
                    <svg class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100 transition-opacity"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"></path>
                    </svg>
                    {{ $option }}
                  </button>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      @endif
    @endforeach

    <!-- AI 思考動畫 -->
    @if($isLoading)
      <div class="flex justify-start animate-fade-in">
        <!-- AI 頭像 -->
        <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                    bg-gradient-to-br from-primary to-primary-dark p-0.5">
          <img src="/agent.png" alt="AI助手"
               class="w-full h-full rounded-full bg-white p-0.5">
        </div>

        <!-- 思考動畫泡泡（無文字） -->
        <div class="bg-white px-5 py-4 rounded-2xl rounded-tl-md
                    shadow-md border border-gray-100">
          <div class="flex space-x-2">
            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"></div>
            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
                 style="animation-delay: 0.1s"></div>
            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
                 style="animation-delay: 0.2s"></div>
          </div>
        </div>
      </div>
    @endif
  </div>

  <!-- Input Area -->
  <div class="p-4 bg-white border-t-2 border-gray-100 rounded-b-none md:rounded-b-2xl flex-shrink-0 shadow-inner">
    <div class="flex items-end space-x-2">
      <textarea
        wire:model.defer="userInput"
        wire:loading.attr="disabled"
        rows="1"
        placeholder="請輸入您的問題..."
        class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
               focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20
               resize-none text-sm leading-relaxed
               hover:border-gray-300 transition-colors
               disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-500"
        wire:keydown.enter.prevent="sendMessage"
        x-data
        x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.call('sendMessage') }"
      ></textarea>

      <button
        wire:click="sendMessage"
        wire:loading.attr="disabled"
        class="w-12 h-12 bg-gradient-to-br from-primary to-primary-dark text-white
               rounded-xl hover:shadow-lg active:scale-95
               transition-all duration-300 flex items-center justify-center flex-shrink-0
               disabled:opacity-50 disabled:cursor-not-allowed
               hover:rotate-12">
        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
      </button>
    </div>

    <p class="text-xs text-gray-400 mt-2 text-center">
      按 Enter 發送 • Shift + Enter 換行
    </p>
  </div>
</div>
```

**主要改進總結**：

1. **互動狀態管理**
   - ✅ 所有互動元素添加 `wire:loading.attr="disabled"`
   - ✅ 輸入框、發送按鈕、快速選項按鈕在載入時自動禁用
   - ✅ 禁用狀態視覺反饋（50%透明、禁用游標）

2. **快速選項按鈕優化**
   - ✅ 改為紫底白字（符合品牌主色）
   - ✅ 只顯示最後一條訊息的按鈕
   - ✅ 載入時自動隱藏（`!$isLoading` 條件）
   - ✅ 按鈕依序動畫出現

3. **載入動畫更新**
   - ✅ 對話泡泡設計（與 AI 訊息一致）
   - ✅ 包含 AI 頭像
   - ✅ 三個跳動點，無文字
   - ✅ 淡入動畫效果

4. **視覺細節優化**
   - ✅ 漸層背景（from-gray-50 to-white）
   - ✅ 訊息氣泡陰影和 hover 效果
   - ✅ AI 頭像漸層邊框
   - ✅ 發送按鈕旋轉動畫

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
