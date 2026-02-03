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
                    
                    <!-- Currency Selector -->
                    <div class="currency-selector relative hide-on-very-small">
                        <button class="currency-button flex items-center gap-2 px-3 py-1.5 rounded hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/30 touch-target"
                                id="currencyButton"
                                aria-label="Select currency">
                            <span class="text-lg font-bold" id="currentCurrencySymbol"><?php echo (isset($currentCurrency) && isset($currencySymbols[$currentCurrency])) ? $currencySymbols[$currentCurrency] : '$'; ?></span>
                            <span id="currentCurrency" class="text-sm font-medium hidden sm:inline"><?php echo isset($currentCurrency) ? $currentCurrency : 'USD'; ?></span>
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
                        
                        <div class="currency-dropdown absolute top-full right-0 mt-2 bg-white rounded-lg shadow-xl min-w-[180px] opacity-0 invisible transform -translate-y-2"
                            id="currencyDropdown">
                            <a href="javascript:void(0)" onclick="changeCurrency('GBP')" class="currency-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100 first:rounded-t-lg" data-currency="GBP">
                                <span class="text-xl font-bold">£</span>
                                <span class="text-sm font-medium">GBP</span>
                                <span class="text-xs text-gray-500 ml-auto">Pounds</span>
                            </a>
                            
                            <a href="javascript:void(0)" onclick="changeCurrency('USD')" class="currency-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-100" data-currency="USD">
                                <span class="text-xl font-bold">$</span>
                                <span class="text-sm font-medium">USD</span>
                                <span class="text-xs text-gray-500 ml-auto">Dollars</span>
                            </a>
                            
                            <a href="javascript:void(0)" onclick="changeCurrency('EUR')" class="currency-option flex items-center gap-3 px-4 py-3 text-gray-800 hover:bg-gray-50 transition-colors duration-150 last:rounded-b-lg" data-currency="EUR">
                                <span class="text-xl font-bold">€</span>
                                <span class="text-sm font-medium">EUR</span>
                                <span class="text-xs text-gray-500 ml-auto">Euros</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>