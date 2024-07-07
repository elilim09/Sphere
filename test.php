<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

    <!DOCTYPE html>
    <html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sphere - 비공식 학교 웹</title>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Noto Sans KR', sans-serif;
                background-color: #f0f0f0;
                color: #333;
            }
            
            header {
                background-color: #00CED1;
                color: white;
                padding: 1rem;
                display: flex;
                justify-content: center;
                align-items: center;
                position: relative;
            }
            
            .logo {
                font-size: 1.5rem;
                font-weight: bold;
            }
            
            #menuBtn {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                position: absolute;
                right: 1rem;
                z-index: 1001;
            }
            
            nav {
                background-color: #008B8B;
                position: fixed;
                top: 0;
                right: -250px;
                width: 250px;
                height: 100%;
                z-index: 1000;
                transition: all 0.3s ease;
                box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
            }
            
            nav.show {
                right: 0;
            }
            
            nav ul {
                list-style-type: none;
                padding-top: 60px;
            }
            
            nav ul li a {
                color: white;
                text-decoration: none;
                display: block;
                padding: 1rem;
                transition: background-color 0.3s ease;
            }
            
            nav ul li a:hover {
                background-color: #006666;
            }
            
            main {
                padding: 1rem;
                transition: margin-right 0.3s ease;
            }
            
            main.shift {
                margin-right: 250px;
            }
            
            section {
                display: none;
                background-color: white;
                padding: 1rem;
                border-radius: 5px;
                margin-bottom: 1rem;
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s ease;
            }
            
            section.active {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }
            
            h2 {
                color: #00CED1;
                margin-bottom: 1rem;
            }
            
            ul {
                list-style-type: none;
            }
            
            li {
                margin-bottom: 0.5rem;
            }
            
            form {
                display: flex;
                flex-direction: column;
            }
            
            input,
            select,
            textarea,
            button {
                margin-bottom: 1rem;
                padding: 0.5rem;
                border: 1px solid #ccc;
                border-radius: 3px;
                transition: border-color 0.3s ease;
            }
            
            input:focus,
            select:focus,
            textarea:focus {
                border-color: #00CED1;
                outline: none;
            }
            
            button {
                background-color: #FFA500;
                color: white;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            
            button:hover {
                background-color: #FF8C00;
            }
        </style>
    </head>

    <body>
        <header>
            <a href="#home" style="text-decoration: none; color: #FFFFFF;">
                <div class="logo">Sphere</div>
            </a>
            <button id="menuBtn">☰</button>
        </header>

        <nav id="menu">
            <ul>
                <li><a href="#home">홈</a></li>
                <li><a href="#login">로그인/회원가입</a></li>
                <li><a href="#board">게시판</a></li>
                <li><a href="#notice">공지사항</a></li>
                <li><a href="#inquiry">문의</a></li>
            </ul>
        </nav>

        <main>
            <section id="home" class="active">
                <h2>최신 공지사항</h2>
                <ul>
                    <li>2024학년도 1학기 수강신청 안내</li>
                    <li>도서관 운영시간 변경 공지</li>
                </ul>
            </section>
            <section id="home" class="active">
                <h2>우리학교 오늘급식</h2>
                <ul>
                    <li>현미밥 <br/>들깨미역국 (5.6)<br/>매운표고돼지갈비찜 (1.5.6.10.13)<br/>잡채 (5.6.10.13)<br/>배추겉절이 (9.17.18)<br/>무지개떡 </li>
                </ul>
            </section>

            <section id="login">
                <h2>로그인</h2>
                <form action="login.php" method="post">
                    <input type="text" name="username" placeholder="아이디" required>
                    <input type="password" name="password" placeholder="비밀번호" required>
                    <button type="submit">로그인</button>
                </form>
                <p><a href="register.php">회원가입</a> | <a href="#">비밀번호 찾기</a></p>
            </section>


            <section id="board">
                <h2>게시판</h2>
                <ul>
                    <li>자유게시판</li>
                    <li>학과별 게시판</li>
                    <li>취업/진로 게시판</li>
                </ul>
            </section>

            <section id="notice">
                <h2>공지사항</h2>
                <ul>
                    <li>2024학년도 1학기 수강신청 안내</li>
                    <li>도서관 운영시간 변경 공지</li>
                    <li>교내 Wi-Fi 업그레이드 안내</li>
                </ul>
            </section>

            <section id="inquiry">
                <h2>문의하기</h2>
                <form>
                    <select required>
                    <option value="">문의 유형 선택</option>
                    <option value="academic">학사 관련</option>
                    <option value="facility">시설 관련</option>
                    <option value="other">기타</option>
                </select>
                    <input type="text" placeholder="제목" required>
                    <textarea placeholder="내용을 입력해주세요" style="height: 100px;" required></textarea>
                    <button type="submit">제출</button>
                </form>
            </section>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const menuBtn = document.getElementById('menuBtn');
                const menu = document.getElementById('menu');
                const main = document.querySelector('main');
                const menuItems = document.querySelectorAll('#menu a');
                const sections = document.querySelectorAll('main section');

                menuBtn.addEventListener('click', function() {
                    menu.classList.toggle('show');
                    main.classList.toggle('shift');
                    this.textContent = this.textContent === '☰' ? '✕' : '☰';
                });

                menuItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href').substring(1);
                        sections.forEach(section => {
                            section.classList.remove('active');
                        });
                        setTimeout(() => {
                            document.getElementById(targetId).classList.add('active');
                        }, 50);
                        menu.classList.remove('show');
                        main.classList.remove('shift');
                        menuBtn.textContent = '☰';
                    });
                });
            });
        </script>
    </body>

    </html>