<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="shortner">
                    <div class="section-heading text-center">
                        <h1> URL Shortener</h1>

                        <div v-if="authorizedUser">
                            <form action="" class="form mt-2">
                                <div class="input-group">
                                    <input class="form-control addUrlInput" id="p1" type="text" placeholder="Paste URL Here" v-model="url" />
                                </div>

                                <div>
                                    <button class="btn btn-dark mt-2" v-on:click.prevent="shortenUrl">
                                        Submit
                                    </button>
                                </div>
                            </form>
                            <p v-if="!urlNotFound" class="alert alert-danger mt-2">
                                URL is not a valid one!
                            </p>

                            <div class="copyLink mb-5">
                                <span id="output_url"></span>
                                <span id="clipBoard" v-on:click.prevent="copyContent">
                                    {{ copyTextString }}
                                </span>
                            </div>

                        </div>

                        <div v-else>
                            <h4>To use this app, You need to login first!</h4>
                            <div class="container">
                                <a class="mt-2 btn btn-info" type="button" href="/register">Register</a>
                                &nbsp;&nbsp;
                                <a class="mt-2 btn btn-primary" type="button" href="/login">Login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
        console.log('Component mounted.')
        console.log('Authorized User:', this.authorizedUser);

    },
    props: ['authorizedUser'],
    data(){
        return {
            url:null,
            urlNotFound: true,
            copyTextString: 'Copy Text To Clipboard',
            response:null,
        }
    },
    methods: {
        isUrlSafe(url) {
            // Make a request to AbuseIPDB API for URL safety check
            return axios.post('https://api.abuseipdb.com/api/v2/check-url', {
                url: url,
                categories: ['ANSI', 'BLOCKING', 'BRANDING', 'CHEATERS', 'COMMUNITY_CHAT', 'DISCORD_INVITE', 'EMAIL', 'MALWARE', 'PHISHING', 'SUSPICIOUS_ACTIVITY', 'SUSPICIOUS_CONTENT', 'SUSPICIOUS_PARTNER', 'WEB_SPAM'],
            }, {
                headers: {
                    'Key': '733eb048dd957a78ea4211d08c6c33d4f286a1dc5d6400c791f0aae28d75d1d465709adc12d39332',
                    'Accept': 'application/json',
                    'Access-Control-Allow-Origin': "true"
                },
            });
        },

        shortenUrl(){
            let self = this;
            let newUrl = self.url;
            let newArray = newUrl.split('//');
            let counter = 0;
            let resultNewUrl = Math.round(Math.pow(36, 7) - Math.random() * Math.pow(36, 7)).toString(36).slice(0, 6);


            for(let i = 0; i < newArray.length; i++){
                if(newArray[i] == 'http:' || newArray[i] == 'https:'){
                    counter++;
                }


                if(counter == 0){
                    let newArrayOne = newUrl.split('.');
                    if(newArrayOne[i] == 'www'){
                        counter++;
                    }


                    let newArrayTwo = newUrl.indexOf('.com');
                    if(newArrayTwo >= 0){
                        counter++;
                    }
                }


                if(counter ==0){
                    self.urlNotFound = false;
                }else{
                    // check url safety first then submit to database!
                    this.isUrlSafe(newUrl).then( response => {
                        console.log(response.data.data);
                        console.log(response.data.data.abuseConfidenceScore >= 50);
                        if(response.data.data.abuseConfidenceScore >= 50){
                            alert("The URL might be unsafe!")
                        } else {
                            let currentUrl = window.location.href+'u/'+resultNewUrl;

                            axios.post('/url/shorten',{
                                url: newUrl,
                                shortlink: currentUrl
                            }).then(function(response){
                                self.response = response.data;
                                $('.copyLink').fadeIn(500);
                                $('.copyLink').siblings('.form').find("#p1").val(self.response);
                                console.log(self.response);
                            });
                        }
                    });
                }
            }
        },
        copyContent() {
            const inputElement = document.getElementById('p1');
            inputElement.select();
            try {
                navigator.clipboard.writeText(inputElement.value)
                    .then(() => {
                        this.copyTextString = 'URL Copied Successfully';
                        this.url = this.response;
                        console.log(this.url);
                    })
                    .catch((err) => {
                        console.error('Unable to copy text to clipboard', err);
                        this.copyTextString = 'Copy failed';
                    });
            } catch (err) {
                console.error('An Error accured: ', err);
                this.copyTextString = 'Copy failed';
            }

            window.getSelection().removeAllRanges();
        }

    }
}
</script>

<style scoped>
    .copyLink{
        display:none;
    }


    #clipBoard{
        display:block;
        margin-top: 28px;
        background-color: #03cbf8;
        color:#fff;
        font-weight: 900;
        font-size:17px;
    }


    #clipBoard:hover{
        background-color:#333;
    }


    #clipBoard:visited, #clipBoard:active, #clipBoard:focus{
        background-color:green;
        color:#333;
    }

</style>
