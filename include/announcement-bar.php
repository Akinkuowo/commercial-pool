<div class="top-menu bg-[#022658] text-white py-2 relative z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-3">
                    <!-- Announcement Slider -->
                    <div class="announcement-container w-full md:w-auto md:flex-1 text-center">
                        <div class="announcement-track">
                            <div class="announcement-item text-sm sm:text-base font-roboto">
                                Fast Delivery
                            </div>
                            <div class="announcement-item text-sm sm:text-base font-roboto">
                                Amazing Brands
                            </div>
                            <div class="announcement-item text-sm sm:text-base font-roboto">
                                Trade Customer Discount
                            </div>
                            <div class="announcement-item text-sm sm:text-base font-roboto">
                                Excellent Trustpilot reviews
                            </div>
                        </div>
                    </div>
                    
                    <!-- Language Selector -->
                    <div class="language-selector relative hide-on-very-small">
                        <button class="language-button flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/30 touch-target"
                                id="languageButton"
                                aria-label="Select language">
                            <span class="flag-icon w-5 h-5 rounded-full overflow-hidden" id="currentFlag">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <circle cx="256" cy="256" fill="#f0f0f0" r="256"/>
                                    <g fill="#0052b4">
                                        <path d="m52.92 100.142c-20.109 26.163-35.272 56.318-44.101 89.077h133.178z"/>
                                        <path d="m503.181 189.219c-8.829-32.758-23.993-62.913-44.101-89.076l-89.075 89.076z"/>
                                        <path d="m8.819 322.784c8.83 32.758 23.993 62.913 44.101 89.075l89.074-89.075z"/>
                                        <path d="m411.858 52.921c-26.163-20.109-56.317-35.272-89.076-44.102v133.177z"/>
                                        <path d="m100.142 459.079c26.163 20.109 56.318 35.272 89.076 44.102v-133.176z"/>
                                        <path d="m189.217 8.819c-32.758 8.83-62.913 23.993-89.075 44.101l89.075 89.075z"/>
                                        <path d="m322.783 503.181c32.758-8.83 62.913-23.993 89.075-44.101l-89.075-89.075z"/>
                                        <path d="m370.005 322.784 89.075 89.076c20.108-26.162 35.272-56.318 44.101-89.076z"/>
                                    </g>
                                    <g fill="#d80027">
                                        <path d="m509.833 222.609h-220.44-.001v-220.442c-10.931-1.423-22.075-2.167-33.392-2.167-11.319 0-22.461.744-33.391 2.167v220.44.001h-220.442c-1.423 10.931-2.167 22.075-2.167 33.392 0 11.319.744 22.461 2.167 33.391h220.44.001v220.442c10.931 1.423 22.073 2.167 33.392 2.167 11.317 0 22.461-.743 33.391-2.167v-220.44-.001h220.442c1.423-10.931 2.167-22.073 2.167-33.392 0-11.317-.744-22.461-2.167-33.391z"/>
                                        <path d="m322.783 322.784 114.236 114.236c5.254-5.252 10.266-10.743 15.048-16.435l-97.802-97.802h-31.482z"/>
                                        <path d="m189.217 322.784h-.002l-114.235 114.235c5.252 5.254 10.743 10.266 16.435 15.048l97.802-97.804z"/>
                                        <path d="m189.217 189.219v-.002l-114.236-114.237c-5.254 5.252-10.266 10.743-15.048 16.435l97.803 97.803h31.481z"/>
                                        <path d="m322.783 189.219 114.237-114.238c-5.252-5.254-10.743-10.266-16.435-15.047l-97.802 97.803z"/>
                                    </g>
                                </svg>
                            </span>
                            <span id="currentLanguage" class="text-sm font-medium hidden sm:inline">English</span>
                            <svg class="w-4 h-4 transition-transform duration-200" 
                                xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 24 24" 
                                fill="none" 
                                stroke="currentColor" 
                                stroke-width="2" 
                                stroke-linecap="round" 
                                stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        
                        <div class="language-dropdown absolute top-full right-0 mt-2 bg-white rounded-lg shadow-xl min-w-[200px] opacity-0 invisible transform -translate-y-2"
                            id="languageDropdown">
                            <a href="/en-gb" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100 first:rounded-t-lg last:rounded-b-lg last:border-b-0">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <circle cx="256" cy="256" fill="#f0f0f0" r="256"/>
                                        <g fill="#0052b4">
                                            <path d="m52.92 100.142c-20.109 26.163-35.272 56.318-44.101 89.077h133.178z"/>
                                            <path d="m503.181 189.219c-8.829-32.758-23.993-62.913-44.101-89.076l-89.075 89.076z"/>
                                            <path d="m8.819 322.784c8.83 32.758 23.993 62.913 44.101 89.075l89.074-89.075z"/>
                                            <path d="m411.858 52.921c-26.163-20.109-56.317-35.272-89.076-44.102v133.177z"/>
                                            <path d="m100.142 459.079c26.163 20.109 56.318 35.272 89.076 44.102v-133.176z"/>
                                            <path d="m189.217 8.819c-32.758 8.83-62.913 23.993-89.075 44.101l89.075 89.075z"/>
                                            <path d="m322.783 503.181c32.758-8.83 62.913-23.993 89.075-44.101l-89.075-89.075z"/>
                                            <path d="m370.005 322.784 89.075 89.076c20.108-26.162 35.272-56.318 44.101-89.076z"/>
                                        </g>
                                        <g fill="#d80027">
                                            <path d="m509.833 222.609h-220.44-.001v-220.442c-10.931-1.423-22.075-2.167-33.392-2.167-11.319 0-22.461.744-33.391 2.167v220.44.001h-220.442c-1.423 10.931-2.167 22.075-2.167 33.392 0 11.319.744 22.461 2.167 33.391h220.44.001v220.442c10.931 1.423 22.073 2.167 33.392 2.167 11.317 0 22.461-.743 33.391-2.167v-220.44-.001h220.442c1.423-10.931 2.167-22.073 2.167-33.392 0-11.317-.744-22.461-2.167-33.391z"/>
                                            <path d="m322.783 322.784 114.236 114.236c5.254-5.252 10.266-10.743 15.048-16.435l-97.802-97.802h-31.482z"/>
                                            <path d="m189.217 322.784h-.002l-114.235 114.235c5.252 5.254 10.743 10.266 16.435 15.048l97.802-97.804z"/>
                                            <path d="m189.217 189.219v-.002l-114.236-114.237c-5.254 5.252-10.266 10.743-15.048 16.435l97.803 97.803h31.481z"/>
                                            <path d="m322.783 189.219 114.237-114.238c-5.252-5.254-10.743-10.266-16.435-15.047l-97.802 97.803z"/>
                                        </g>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">English</span>
                                <span class="text-xs text-gray-500 ml-auto">(GBP)</span>
                            </a>
                            
                            <a href="/da-dk" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <circle cx="256" cy="256" fill="#f0f0f0" r="256"/>
                                        <g fill="#d80027">
                                            <path d="m200.349 222.609h309.484c-16.363-125.607-123.766-222.609-253.833-222.609-19.115 0-37.732 2.113-55.652 6.085v216.524z"/>
                                            <path d="m133.565 222.608v-191.481c-70.293 38.354-120.615 108.705-131.398 191.482h131.398z"/>
                                            <path d="m133.564 289.391h-131.397c10.783 82.777 61.105 153.128 131.398 191.481z"/>
                                            <path d="m200.348 289.392v216.523c17.92 3.972 36.537 6.085 55.652 6.085 130.067 0 237.47-97.002 253.833-222.609h-309.485z"/>
                                        </g>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">dansk</span>
                            </a>
                            
                            <a href="/de-de" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#ffda44" d="m0 345 256.7-25.5L512 345v167H0z"/>
                                        <path fill="#d80027" d="m0 167 255-23 257 23v178H0z"/>
                                        <path fill="#333" d="M0 0h512v167H0z"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Deutsch</span>
                            </a>
                            
                            <a href="/fr-fr" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#fff" d="M167 0h178l25.9 252.3L345 512H167l-29.8-253.4z"/>
                                        <path fill="#002654" d="M0 0h167v512H0z"/>
                                        <path fill="#ce1126" d="M345 0h167v512H345z"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">français</span>
                            </a>
                            
                            <a href="/nl-nl" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <circle cx="256" cy="256" fill="#f0f0f0" r="256"/>
                                        <path d="m256 0c-110.071 0-203.906 69.472-240.077 166.957h480.155c-36.172-97.485-130.007-166.957-240.078-166.957z" fill="#a2001d"/>
                                        <path d="m256 512c110.071 0 203.906-69.472 240.077-166.957h-480.154c36.171 97.485 130.006 166.957 240.077 166.957z" fill="#0052b4"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Nederlands</span>
                            </a>
                            
                            <a href="/sv-se" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="#0052b4" d="M0 0h133.6l35.3 16.7L200.3 0H512v222.6l-22.6 31.7 22.6 35.1V512H200.3l-32-19.8-34.7 19.8H0V289.4l22.1-33.3L0 222.6z"/>
                                        <path fill="#ffda44" d="M133.6 0v222.6H0v66.8h133.6V512h66.7V289.4H512v-66.8H200.3V0z"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">svenska</span>
                            </a>
                            
                            <a href="/en-gb/-1" class="language-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150">
                                <span class="flag-icon w-5 h-5 rounded-full overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <rect width="512" height="512" rx="307.2" fill="#222999"/>
                                        <path fill="#fbd017" d="M256 46.305l-9.404 19.054-21.03 3.056 15.217 14.832-3.592 20.945L256 94.305l18.81 9.888-3.593-20.945 15.217-14.832-21.03-3.057L256 46.304z"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">English</span>
                                <span class="text-xs text-gray-500 ml-auto">(EUR)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>