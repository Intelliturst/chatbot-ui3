# 虹宇職訓智能客服系統 - JSON知識庫設計規範

**文件版本**: 1.0
**最後更新**: 2025-10-24
**作者**: 虹宇職訓開發團隊

---

## 📋 目錄

1. [知識庫目錄結構](#知識庫目錄結構)
2. [課程資料JSON](#課程資料json)
3. [補助規則JSON](#補助規則json)
4. [常見問題JSON](#常見問題json)
5. [聯絡資訊JSON](#聯絡資訊json)
6. [打招呼與未知分類JSON](#打招呼與未知分類json)
7. [快速選項JSON](#快速選項json)
8. [RAG查詢邏輯](#rag查詢邏輯)
9. [資料維護指南](#資料維護指南)

---

## 知識庫目錄結構

```
src/main/resources/config/chatbot/knowledge_base/
├── courses/
│   ├── course_list.json          # 課程清單（約20門課程）
│   ├── course_mapping.json       # 課程編號對應表
│   └── default_course.jpg        # 預設課程圖片
│
├── subsidy/
│   ├── employed_rules.json       # 在職補助規則
│   ├── unemployed_rules.json     # 待業補助規則
│   └── subsidy_faq.json          # 補助常見問答
│
├── faq/
│   ├── general_faq.json          # 一般常見問題
│   ├── enrollment_process.json   # 報名流程說明
│   └── related_questions.json    # 關聯問題庫
│
├── contacts/
│   └── service_info.json         # 聯絡資訊
│
├── greetings/
│   └── default_responses.json    # 打招呼/未知分類回覆
│
├── quick_options/
│   └── button_config.json        # 快速選項按鈕配置
│
└── classification/
    ├── categories.json            # 9個分類定義
    └── keywords_mapping.json      # 關鍵字對應表
```

---

## 課程資料JSON

### course_list.json

基於實際課程資料（courses/6 和 courses/12），設計完整的課程資料結構。

```json
{
  "courses": [
    {
      "id": 6,
      "course_code": "158352",
      "course_name": "AI生成影音行銷與直播技巧實戰班",
      "full_name": "待業-1141128 AI生成影音行銷與直播技巧實戰班",
      "type": "unemployed",
      "featured": 1,

      "schedule": {
        "start_date": "2025-11-28",
        "end_date": "2026-01-30",
        "class_time": "週一至週五，上午9:00～下午17:00",
        "total_hours": 300,
        "enrollment_deadline": "2025-11-18 17:30",
        "interview_date": "2025-11-18 13:00"
      },

      "fee": {
        "amount": "政府補助100%",
        "note": "一般國民、未具特定對象身分且參加工/農/漁會勞工保險者，需自行負擔20%"
      },

      "enrollment": {
        "capacity": 30,
        "requirements": "15歲以上失業者，具工作意願；身心障礙者優先"
      },

      "location": {
        "address": "桃園市中壢區復興路46號12樓（兆豐銀行樓上）",
        "map_url": ""
      },

      "content": "涵蓋影像編修、AI生成式設計、商品視覺化、短影音製作、社群經營、直播企劃與平台策略等22個教學單元，總計約290小時專業課程。招生人數30人，15歲以上失業者具工作意願者皆可報名，身心障礙者優先錄取。",

      "url": "https://www.hongyu.goblinlab.org/courses/6",
      "image_url": "/images/courses/course_6.jpg",

      "keywords": ["AI", "影音", "行銷", "直播", "短影音", "社群經營", "影像編修"],
      "priority": 1,

      "related_questions": [
        "報名截止時間",
        "上課地點",
        "課程內容詳情",
        "如何報名",
        "補助資格確認"
      ]
    },

    {
      "id": 12,
      "course_code": "167567",
      "course_name": "PMP專案管理實務班",
      "full_name": "產投-114/12/28(週日)PMP專案管理實務班",
      "type": "employed",
      "featured": 0,

      "schedule": {
        "start_date": "2025-12-28",
        "end_date": "2026-02-08",
        "class_time": "每週日，09:00-17:00",
        "total_hours": 47,
        "enrollment_period": "2025/11/28 12:00 ~ 2025/12/25 18:00"
      },

      "fee": {
        "amount": "8,930元",
        "payment_method": "報名時須繳全額（現金或轉帳，不接受刷卡）",
        "subsidy_capacity": 28,
        "note": "結訓後可申請80%補助"
      },

      "enrollment": {
        "requirements": "國中以上、年滿15歲、電腦基本操作能力"
      },

      "location": {
        "address": "桃園市中壢區復興路46號12樓（兆豐銀行樓上）"
      },

      "content": "共47小時課程，涵蓋專案管理整體架構（PMBOK第七版）、商業分析與使用者故事、專案啟動與利害關係人管理、專案規劃目標設定與關鍵行動、成本預算與實獲值管理、品質工具風險分析與採購分析、微軟Project與PowerPoint應用、PMP考試複習等內容。適合想考取PMP證照或提升專案管理能力的在職人員。",

      "url": "https://www.hongyu.goblinlab.org/courses/12",
      "image_url": "/images/courses/course_12.jpg",

      "keywords": ["PMP", "專案管理", "PMBOK", "Project", "管理實務"],
      "priority": 2,

      "related_questions": [
        "報名期間",
        "上課地點",
        "課程費用",
        "補助資訊",
        "報名方式"
      ]
    }
  ],

  "meta": {
    "total_courses": 20,
    "last_updated": "2025-10-24",
    "version": "1.0"
  }
}
```

### course_mapping.json

用於快速查找課程編號對應

```json
{
  "1": 6,
  "2": 12,
  "3": 15,
  "mapping": {
    "待業課程": [6, 15, 18],
    "在職課程": [12, 14, 16],
    "精選課程": [6, 12]
  }
}
```

---

## 補助規則JSON

### employed_rules.json（在職補助規則）

```json
{
  "category": "在職補助",
  "rules": [
    {
      "subsidy_rate": "80%",
      "title": "一般在職補助（80%）",
      "description": "符合在職勞工身份即可申請80%課程費用補助",
      "requirements": [
        "年滿15歲以上",
        "在課程開訓當日為在職勞工",
        "具備就業保險、勞工保險、勞工職業災害保險、農民健康保險任一種"
      ],
      "process": [
        "先繳交全額課程費用",
        "完成課程並結訓",
        "向開課單位申請補助",
        "經審核後撥款（約3-5個工作天）"
      ],
      "note": "學員需先自費繳交全額學費，待課程順利結訓後，才能申請補助款項。資格認定是以「開訓日」當天的投保狀態為準。"
    },
    {
      "subsidy_rate": "100%",
      "title": "特定身份全額補助（100%）",
      "description": "符合特定身份的在職勞工可申請100%補助",
      "requirements": [
        "必須先符合一般在職補助的所有條件",
        "並符合以下至少一項特定身份："
      ],
      "special_identities": [
        "低收入戶或中低收入戶中有工作能力者",
        "原住民",
        "身心障礙者",
        "中高齡者（年滿45歲至65歲）",
        "逾65歲之高齡者",
        "獨力負擔家計者",
        "家庭暴力被害人",
        "更生受保護人",
        "因犯罪行為被害之特定關係人",
        "其他依《就業服務法》第24條規定經中央主管機關認為有必要者"
      ],
      "documents_required": [
        "相關證明文件（如：中低收入戶證明、身心障礙證明等）"
      ],
      "note": "申請時請務必備妥相關證明文件，並以開課單位的最終要求為準。"
    }
  ],
  "priority": 1
}
```

### unemployed_rules.json（待業補助規則）

```json
{
  "category": "待業補助",
  "rules": [
    {
      "subsidy_rate": "100%",
      "title": "特定對象全額補助（100%）",
      "description": "符合特定對象身份的失業者可獲得全額補助",
      "requirements": [
        "符合以下任一條件："
      ],
      "conditions": [
        {
          "type": "就業保險被保險人失業者",
          "description": "含自願與非自願離職"
        },
        {
          "type": "就業服務法第24條特定對象失業者",
          "list": [
            "獨力負擔家計者",
            "中高齡者",
            "身心障礙者",
            "原住民",
            "低收入戶或中低收入戶",
            "長期失業者",
            "二度就業婦女",
            "家庭暴力被害人",
            "更生受保護人"
          ]
        },
        {
          "type": "其他弱勢對象失業者",
          "list": [
            "新住民",
            "性侵害被害人",
            "逾65歲高齡者"
          ]
        }
      ],
      "note": "完全符合條件者可享有100%訓練課程學費補助"
    },
    {
      "subsidy_rate": "80%",
      "title": "一般失業者部分補助（自付20%）",
      "description": "不符合全額補助條件的一般失業者",
      "requirements": [
        "不符合上述全額補助條件的失業者"
      ],
      "note": "學員需自行負擔20%的訓練費用。建議在報名前，向訓練單位確認詳細的費用與申請流程。"
    }
  ],
  "priority": 1
}
```

### subsidy_faq.json

```json
{
  "category": "補助常見問答",
  "faqs": [
    {
      "question": "我如何知道自己是否符合補助資格？",
      "answer": "請告訴我您目前是「在職」還是「待業」，我會協助您判斷補助資格。",
      "category": "補助資格",
      "keywords": ["補助資格", "是否符合", "如何申請"]
    },
    {
      "question": "補助什麼時候會撥款？",
      "answer": "在職課程補助：完成課程並結訓後，向開課單位申請補助，經審核後約3-5個工作天撥款。\n待業課程補助：通常在開課前或開課時即已扣除補助金額，學員只需繳交自付額（如有）。",
      "category": "補助申請",
      "keywords": ["撥款", "補助時間", "何時", "多久"]
    },
    {
      "question": "需要準備什麼證明文件？",
      "answer": "依您的身份而定：\n- 一般在職者：投保證明（勞保、就保等）\n- 特定身份：相關證明文件（低收入戶證明、身心障礙證明、原住民證明等）\n具體請以開課單位的最終要求為準。",
      "category": "補助申請",
      "keywords": ["證明文件", "需要", "準備", "文件"]
    }
  ]
}
```

---

## 常見問題JSON

### general_faq.json

```json
{
  "category": "一般常見問題",
  "faqs": [
    {
      "id": "faq_001",
      "question": "如何報名課程？",
      "answer": "您可以透過以下方式報名：\n\n1. 線上報名：請至課程網頁填寫報名表單\n2. 電話報名：03-4227723、03-4259355\n3. LINE報名：加入官方帳號 @ouy9482x\n4. 親臨報名：桃園市中壢區復興路46號12樓\n\n【在職課程】：透過「就業通」線上報名\n【待業課程】：需參加甄試，請於報名截止前完成報名",
      "category": "報名相關",
      "keywords": ["報名", "如何", "方式"],
      "priority": 1,
      "related_questions": ["報名截止時間", "甄試流程", "需要準備什麼"]
    },
    {
      "id": "faq_002",
      "question": "上課地點在哪裡？",
      "answer": "所有課程的上課地點統一在：\n\n📍 桃園市中壢區復興路46號12樓（兆豐銀行樓上）\n\n交通方式：\n- 公車：於中壢火車站搭乘多班公車可達\n- 開車：附近有收費停車場\n- 捷運：（規劃中）",
      "category": "上課相關",
      "keywords": ["地點", "在哪", "地址", "位置"],
      "priority": 2,
      "related_questions": ["交通方式", "停車資訊"]
    },
    {
      "id": "faq_003",
      "question": "可以請假嗎？",
      "answer": "可以請假，但請注意：\n\n【待業課程】：\n- 缺課時數不得超過總時數的20%\n- 超過規定缺課時數將影響結業證書核發\n\n【在職課程】：\n- 建議全程參與以確保學習效果\n- 缺課時數可能影響補助申請資格\n\n請假請提前聯絡：03-4227723",
      "category": "上課相關",
      "keywords": ["請假", "缺課", "不能來"],
      "priority": 3
    },
    {
      "id": "faq_004",
      "question": "結業證書如何取得？",
      "answer": "結業證書取得條件：\n\n1. 出席率達80%以上\n2. 完成所有課程作業\n3. 通過結業測驗（如有）\n\n符合條件者，於課程結束後約2週內核發結業證書。\n證書可選擇：\n- 現場領取\n- 郵寄到府（需負擔郵資）",
      "category": "證書相關",
      "keywords": ["證書", "結業", "如何取得"],
      "priority": 4
    }
  ]
}
```

### enrollment_process.json

```json
{
  "category": "報名流程說明",
  "employed_process": {
    "title": "在職課程報名流程",
    "steps": [
      {
        "step": 1,
        "title": "線上報名",
        "description": "前往「就業通」網站進行線上報名",
        "url": "https://www.taiwanjobs.gov.tw"
      },
      {
        "step": 2,
        "title": "繳交費用",
        "description": "報名時須繳交全額課程費用（現金或轉帳）",
        "note": "不接受刷卡"
      },
      {
        "step": 3,
        "title": "確認開課",
        "description": "開課前3天會以電話或簡訊通知"
      },
      {
        "step": 4,
        "title": "準時上課",
        "description": "請於開課當日準時到場，攜帶身分證件"
      },
      {
        "step": 5,
        "title": "完成課程",
        "description": "出席率達80%以上，完成所有作業"
      },
      {
        "step": 6,
        "title": "申請補助",
        "description": "結訓後向開課單位申請80%補助",
        "documents": [
          "結業證書",
          "投保證明",
          "身分證影本",
          "存摺影本"
        ]
      }
    ]
  },
  "unemployed_process": {
    "title": "待業課程報名流程",
    "steps": [
      {
        "step": 1,
        "title": "線上/電話報名",
        "description": "填寫報名表單，提供基本資料",
        "deadline": "請於報名截止日前完成報名"
      },
      {
        "step": 2,
        "title": "參加甄試",
        "description": "依通知時間參加甄試（筆試或面試）",
        "documents": [
          "身分證正本",
          "相關證明文件（如有特定身份）"
        ]
      },
      {
        "step": 3,
        "title": "錄取通知",
        "description": "甄試後3-5個工作天公告錄取名單"
      },
      {
        "step": 4,
        "title": "報到繳費",
        "description": "依錄取通知指示完成報到手續",
        "note": "特定對象可能享有全額補助，僅需自付20%或免費"
      },
      {
        "step": 5,
        "title": "準時上課",
        "description": "課程為全日制（週一至週五 9:00-17:00）"
      },
      {
        "step": 6,
        "title": "取得證書",
        "description": "出席率達80%以上即可取得結業證書"
      }
    ]
  }
}
```

---

## 聯絡資訊JSON

### service_info.json

```json
{
  "organization": {
    "name": "虹宇職訓",
    "full_name": "虹宇桃園職訓中心",
    "logo_url": "/public/logo.png",
    "brand_color": "#4F46E5"
  },

  "contact": {
    "phone": {
      "main": ["03-4227723", "03-4259355", "03-3378075"],
      "display": "03-4227723",
      "available_hours": "週一至週五 9:00-18:00"
    },
    "email": {
      "general": "atcd89@hongyu.com.tw",
      "support": "service@hongyu.com.tw"
    },
    "line": {
      "id": "@ouy9482x",
      "qrcode_url": "/images/line_qrcode.jpg",
      "link": "https://line.me/R/ti/p/@ouy9482x"
    },
    "address": {
      "full": "桃園市中壢區復興路46號12樓",
      "note": "兆豐銀行樓上",
      "map_url": "https://maps.google.com/?q=桃園市中壢區復興路46號12樓"
    }
  },

  "service_hours": {
    "weekdays": "週一至週五 9:00-18:00",
    "weekend": "週六日及國定假日休息",
    "note": "如有緊急事項，可透過LINE留言，我們會盡快回覆"
  },

  "staff": {
    "course_inquiry": ["林小姐", "何小姐"],
    "subsidy_inquiry": ["李小姐", "陳小姐"],
    "general_inquiry": ["客服團隊"]
  },

  "response_template": {
    "contact_info": "📞 聯絡方式\n\n電話：{phone}\n營業時間：{hours}\n\nEmail：{email}\n\nLINE官方：{line}\n\n地址：{address}\n{note}",
    "urgent": "如有緊急事項，歡迎直接撥打客服電話：{phone}，或加入LINE官方帳號：{line}"
  }
}
```

---

## 打招呼與未知分類JSON

### default_responses.json

```json
{
  "greetings": {
    "category": "打招呼",
    "responses": [
      {
        "trigger": ["hi", "hello", "哈囉", "你好", "嗨"],
        "response": "您好！我是虹宇職訓的智能客服小幫手 👋\n\n我可以協助您：\n1️⃣ 查詢課程資訊\n2️⃣ 了解補助資格\n3️⃣ 報名流程說明\n4️⃣ 常見問題解答\n\n請問有什麼可以幫您的呢？",
        "quick_options": [
          "查看課程清單",
          "補助資格確認",
          "如何報名",
          "聯絡真人客服"
        ]
      },
      {
        "trigger": ["早安", "午安", "晚安"],
        "response": "{greeting}！歡迎來到虹宇職訓 ☀️\n\n今天想了解什麼課程或服務呢？",
        "quick_options": [
          "待業課程",
          "在職課程",
          "熱門課程"
        ]
      },
      {
        "trigger": ["謝謝", "感謝", "辛苦了"],
        "response": "不客氣！很高興能幫助您 😊\n\n如果還有其他問題，隨時歡迎詢問。\n\n祝您學習愉快！",
        "quick_options": [
          "查看更多課程",
          "聯絡真人客服"
        ]
      }
    ],
    "priority": 1
  },

  "unknown": {
    "category": "未知分類",
    "responses": [
      {
        "default": "抱歉，我暫時無法理解您的問題 🤔\n\n您可以：\n1. 換個方式描述您的問題\n2. 選擇以下常見主題\n3. 聯絡真人客服獲得協助",
        "quick_options": [
          "課程查詢",
          "補助諮詢",
          "報名流程",
          "聯絡客服"
        ]
      },
      {
        "fallback": "很抱歉，我僅能協助您關於虹宇職訓的課程與服務相關問題。\n\n如需其他協助，歡迎聯絡我們的真人客服：\n📞 03-4227723\n💬 LINE: @ouy9482x",
        "quick_options": [
          "回到主選單",
          "聯絡真人客服"
        ]
      }
    ],
    "priority": 2
  }
}
```

---

## 快速選項JSON

### button_config.json

```json
{
  "quick_options": {
    "main_menu": [
      {
        "label": "課程查詢",
        "icon": "📚",
        "trigger": "course_inquiry",
        "description": "查看所有課程資訊"
      },
      {
        "label": "補助諮詢",
        "icon": "💰",
        "trigger": "subsidy_inquiry",
        "description": "了解補助資格與申請"
      },
      {
        "label": "報名流程",
        "icon": "📝",
        "trigger": "enrollment_process",
        "description": "如何報名課程"
      },
      {
        "label": "聯絡客服",
        "icon": "☎️",
        "trigger": "contact_service",
        "description": "獲得真人協助"
      }
    ],

    "course_menu": [
      {
        "label": "待業課程",
        "trigger": "unemployed_courses"
      },
      {
        "label": "在職課程",
        "trigger": "employed_courses"
      },
      {
        "label": "熱門課程",
        "trigger": "featured_courses"
      },
      {
        "label": "搜尋課程",
        "trigger": "search_courses"
      }
    ],

    "subsidy_menu": [
      {
        "label": "我是在職者",
        "trigger": "employed_subsidy"
      },
      {
        "label": "我是待業者",
        "trigger": "unemployed_subsidy"
      },
      {
        "label": "不確定身份",
        "trigger": "subsidy_help"
      }
    ]
  },

  "related_questions": {
    "course": [
      "報名截止時間",
      "上課地點",
      "課程費用",
      "補助資訊",
      "如何報名"
    ],
    "subsidy": [
      "需要什麼文件",
      "補助多少錢",
      "何時撥款",
      "申請流程"
    ],
    "enrollment": [
      "報名方式",
      "需要準備什麼",
      "甄試流程",
      "錄取通知"
    ]
  }
}
```

---

## RAG查詢邏輯

### RAG Service 實現邏輯

```php
// src/main/php/Services/RAGService.php

class RAGService
{
    /**
     * 查詢課程資料
     */
    public function queryCourses($filters = [])
    {
        $courses = $this->loadJSON('courses/course_list.json');

        // 篩選條件
        if (isset($filters['type'])) {
            $courses = array_filter($courses, function($course) use ($filters) {
                return $course['type'] === $filters['type'];
            });
        }

        if (isset($filters['featured']) && $filters['featured']) {
            $courses = array_filter($courses, function($course) {
                return $course['featured'] === 1;
            });
        }

        if (isset($filters['keyword'])) {
            $courses = $this->searchByKeyword($courses, $filters['keyword']);
        }

        // 按優先級排序
        usort($courses, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $courses;
    }

    /**
     * 關鍵字搜索
     */
    protected function searchByKeyword($courses, $keyword)
    {
        return array_filter($courses, function($course) use ($keyword) {
            // 搜尋課程名稱
            if (stripos($course['course_name'], $keyword) !== false) {
                return true;
            }

            // 搜尋關鍵字陣列
            foreach ($course['keywords'] as $kw) {
                if (stripos($kw, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * 查詢單一課程
     */
    public function getCourseById($id)
    {
        $courses = $this->loadJSON('courses/course_list.json');

        foreach ($courses['courses'] as $course) {
            if ($course['id'] == $id) {
                return $course;
            }
        }

        return null;
    }

    /**
     * 查詢補助規則
     */
    public function getSubsidyRules($type)
    {
        $filename = $type === 'employed'
            ? 'subsidy/employed_rules.json'
            : 'subsidy/unemployed_rules.json';

        return $this->loadJSON($filename);
    }

    /**
     * 查詢FAQ
     */
    public function searchFAQ($keyword)
    {
        $faqData = $this->loadJSON('faq/general_faq.json');

        $results = [];
        foreach ($faqData['faqs'] as $faq) {
            // 搜尋問題
            if (stripos($faq['question'], $keyword) !== false) {
                $results[] = $faq;
                continue;
            }

            // 搜尋關鍵字
            foreach ($faq['keywords'] as $kw) {
                if (stripos($kw, $keyword) !== false) {
                    $results[] = $faq;
                    break;
                }
            }
        }

        // 按優先級排序
        usort($results, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $results;
    }

    /**
     * 取得預設回應（打招呼/未知分類）
     */
    public function getDefaultResponse($category, $trigger = null)
    {
        $responses = $this->loadJSON('greetings/default_responses.json');

        if ($category === 'greeting') {
            foreach ($responses['greetings']['responses'] as $response) {
                if (in_array($trigger, $response['trigger'])) {
                    return $response;
                }
            }
            return $responses['greetings']['responses'][0];
        }

        if ($category === 'unknown') {
            return $responses['unknown']['responses'][0];
        }

        return null;
    }

    /**
     * 載入JSON文件
     */
    protected function loadJSON($filename)
    {
        $path = resource_path("config/chatbot/knowledge_base/{$filename}");

        if (!file_exists($path)) {
            throw new \Exception("Knowledge base file not found: {$filename}");
        }

        return json_decode(file_get_contents($path), true);
    }
}
```

### 查詢範例

```php
// 查詢待業課程
$ragService = new RAGService();
$courses = $ragService->queryCourses(['type' => 'unemployed']);

// 查詢精選課程
$featuredCourses = $ragService->queryCourses(['featured' => true]);

// 關鍵字搜尋
$aiCourses = $ragService->queryCourses(['keyword' => 'AI']);

// 查詢單一課程
$course = $ragService->getCourseById(6);

// 查詢補助規則
$rules = $ragService->getSubsidyRules('employed');

// 搜尋FAQ
$faqResults = $ragService->searchFAQ('報名');
```

---

## 資料維護指南

### 新增課程

1. 編輯 `course_list.json`
2. 在 `courses` 陣列中新增課程物件
3. 確保包含所有必要欄位
4. 更新 `meta.total_courses` 和 `meta.last_updated`
5. 更新 `course_mapping.json` 的對應關係

### 修改補助規則

1. 編輯對應的 `employed_rules.json` 或 `unemployed_rules.json`
2. 更新規則內容
3. 同步更新 `subsidy_faq.json` 相關問答

### 新增FAQ

1. 編輯 `general_faq.json`
2. 在 `faqs` 陣列中新增FAQ物件
3. 設定適當的 `keywords` 和 `priority`
4. 考慮新增 `related_questions`

### 資料驗證

建議建立一個驗證腳本檢查JSON格式：

```php
// validate_knowledge_base.php

$files = [
    'courses/course_list.json',
    'subsidy/employed_rules.json',
    'subsidy/unemployed_rules.json',
    'faq/general_faq.json',
    // ... 其他文件
];

foreach ($files as $file) {
    $path = resource_path("config/chatbot/knowledge_base/{$file}");
    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ {$file}: " . json_last_error_msg() . "\n";
    } else {
        echo "✅ {$file}: Valid\n";
    }
}
```

---

## 附錄

### 相關文件

- [01-system-architecture.md](./01-system-architecture.md) - 系統架構設計
- [03-agent-implementation.md](./03-agent-implementation.md) - 代理實現規範
- [05-course-api-integration.md](./05-course-api-integration.md) - Course API對接設計

---

**文件結束**
