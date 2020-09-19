<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
        rel="stylesheet">
        
        <!-- Style -->
        <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
        <style>
            .modal {
                z-index: 2;
                overflow-x: hidden;
                overflow-y: auto;
                position: absolute;
                top: 100px;
                left: 0;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div id="app">
            {{-- <div class="container flex mx-auto">
                <div class="asset-list w-full my-10 border rounded-lg py-5 px-10">
                    <div class="head flex flex-row justify-between">
                        <div class="search-list flex items-center border rounded px-5">
                            <div class="icon mr-1">
                                <svg style="width:24px;height:24px" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                placeholder="Search asset"
                                class="p-3 w-full focus:outline-none" 
                            >
                        </div>
                    </div>
                    <div class="body mt-5">
                        <div class="list p-5 w-full grid grid-cols-12 border gap-4">
                            <div class="image">
                                <img 
                                    src="{{asset('storage/assets/asset-Asus ROG-1600512094.jpg')}}" 
                                    alt="asset image"
                                    class="rounded-lg"
                                >
                            </div>
                            <div class="px-5">Laptop</div>
                            <div class="px-5">Laptop Suangar</div>
                            <div class="px-5">19 Sep 2020 18:30 </div>
                            <div class="px-5">19 Sep 2020 18:30</div>
                            <div class="px-5">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div 
                class="p-5 border rounded w-1/2 modal" id="modalImage"
                :class="{ hidden: !modalShown }"
            >
                <div class="flex justify-center w-full">
                    <img 
                        class="rounded pb-4 " 
                        src="{{asset('storage/assets/asset-Asus ROG-1600512094.jpg')}}" 
                        alt="image"
                    >
                </div>
    
                <p>Title</p>
                <p class="text-sm">
                    Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quaerat laboriosam doloremque laborum officiis, earum ex minus error, repellat magni velit iure officia dolores laudantium. Odit veniam molestiae perspiciatis mollitia voluptates.
                </p>
            </div>
    
            <div class="container flex flex-col items-center p-4 justify-center w-full">
                <div 
                    class="py-2 px-4 bg-blue-700 text-white rounded hover:bg-blue-900 width-1/4"
                    id="toggleBtn"
                    @click="toggleModal"
                >
                    View Image
                </div>
            </div>
        </div>
    </body>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
    <script>
        // const modalImage = document.getElementById("modalImage")
        // const toggleBtn = document.getElementById("toggleBtn")  
        
        // toggleBtn.addEventListener('click', e => {
        //     modalImage.classList.toggle('hidden')
        // })

        new Vue({
            el: '#app',
            data: {
                modalShown: false
            },
            methods: {
                toggleModal() {
                    this.modalShown = !this.modalShown
                }
            }
        })

    </script>
</html>
