<template>
	<div>
      <div class="polis-addresses">
        <template v-for="(police, index) in polices">
            <template v-if="index == 0">
                <div class="polis-address">
                    <img src="/images/done_shild.svg" class="polis-address__img" v-if="setStatus(police.status_id)" />
                    <img src="/images/error_shild.svg" class="polis-address__img" v-else />
                    <p class="polis-address__text">
                        {{ police.address }}, {{ police.appartment }}
                    </p>
                </div>
            </template>
            <template v-else>
                <div class="polis-address" v-bind:class="{error : !setStatus(police.status_id)}" v-on:click="choosePolice(police.id)">
                    <img src="/images/done_shild-invert.svg" class="polis-address__img" v-if="setStatus(police.status_id)" />
                    <img src="/images/error_shild-invert.svg" class="polis-address__img" v-else />
                    <p class="polis-address__text">
                        {{ police.address }}, {{ police.appartment }}
                    </p>
                </div>
            </template>
        </template>
            <button class="button polis-address__button" type="button" @click="addSecondPolic">
                <svg class="svg">
                  <use xlink:href="#plus"></use>
                </svg>

                Добавить <br />
                полис
            </button>
      </div>
	</div>
</template>

<script>
    export default {
    	  props: ["id", "polices"],
    	  mounted() {
            console.log('inside_police', this.polices)
    	  },
        methods: {
            choosePolice($id) {
                this.$emit('chosenPolice', $id)
            },
            setStatus($status_id) {
                if($status_id == 1 || $status_id == 2) 
                  return true

                return false
            },
            addSecondPolic() {
                axios.get('/api/v1/add_aPolic').then(response => {
                    if(response.data.status && response.data.data) {
                      this.$router.push('/authaddress')
                    }
                })
            }
        }
    }
</script>