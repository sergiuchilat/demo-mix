<script lang="ts">
import Vue from "vue";
import ImageViewer from "@/components/viewers/Image.vue";

export default Vue.extend({
  name: "FileUploader",

  components: { ImageViewer },

  props: {
    value: {
      required: false,
      default: null
    },
    label: {
      required: false,
      type: String,
      default: ""
    },
    rules: {
      required: false,
      type: Array,
      default: () => []
    },
    accept: {
      required: false,
      type: String,
      default: ""
    },
    truncateLength: {
      required: false,
      type: Number,
      default: 46
    },
    dense: {
      required: false,
      type: Boolean,
      default: false
    },
    outlined: {
      required: false,
      type: Boolean,
      default: false
    },
    filled: {
      required: false,
      type: Boolean,
      default: false
    },
    autofocus: {
      required: false,
      type: Boolean,
      default: false
    },
    hideDetails: {
      required: false,
      type: Boolean,
      default: false
    },
    onlyImage: {
      required: false,
      type: Boolean,
      default: false
    },
    width: {
      type: String,
      default: "100%",
      required: false
    },
    height: {
      type: String,
      default: "100%",
      required: false
    },
    title: {
      type: String,
      default: "",
      required: false
    },
    errorMessages: {
      type: Array,
      default: () => [],
      required: false
    }
  },

  data: () => ({
    model: null as File | null,
    imagePreview: "" as string | ArrayBuffer | null,
    defaultImage: require("@/assets/media/default-img.png") as File | any
  }),

  watch: {
    value: {
      immediate: true,
      handler(): void {
        const image: any = this.value;

        if (image && image.hasOwnProperty("url")) {
          this.model = new File([], image.name);
          this.imagePreview = image.url;
        } else {
          this.model = this.value;
        }
      }
    }
  },

  methods: {
    toBase64(file: File): Promise<string | ArrayBuffer | null> {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
      });
    },
    async changeData(): Promise<void> {
      if (this.model?.type.includes("image")) {
        this.imagePreview = await this.toBase64(this.model);
      } else {
        this.imagePreview = "";
      }

      this.$emit("input", this.model);
    },
    onFileChanged(e: any): void {
      this.model = e.target.files[0];
      this.changeData();
    },
    handleFileImport(): void {
      (this.$refs.uploader as Vue).click();
    }
  }
});
</script>

<template>
  <div class="full-width">
    <v-file-input
      v-model="model"
      :accept="accept"
      :autofocus="autofocus"
      :dense="dense"
      :filled="filled"
      :hide-details="hideDetails"
      :label="label"
      :outlined="outlined"
      :rules="rules"
      :truncate-length="truncateLength"
      prepend-icon=""
      ref="uploader"
      v-if="!onlyImage"
      @change="changeData()"
    >
      <template #prepend-inner>
        <image-viewer
          v-if="model && model.type.includes('image')"
          :size="24"
          :src="imagePreview"
          rounded
          show-preview
        ></image-viewer>
        <a
          v-else-if="imagePreview"
          :href="imagePreview"
          class="text-decoration-none"
          target="_blank"
        >
          <v-icon color="primary">mdi-tray-arrow-down</v-icon>
        </a>
        <v-icon v-else>mdi-paperclip</v-icon>
      </template>
    </v-file-input>
    <div v-else>
      <div class="font-weight-bold">{{ label }}</div>
      <v-avatar
        :width="width"
        :height="height"
        tile
        color="transparent"
        class="user-avatar avatar"
      >
        <v-img
          :src="imagePreview || defaultImage"
          class="avatar"
          style="border: 1px solid #cbcaca"
        />
        <span
          @click="handleFileImport"
          :title="title"
          class="display-icon new-avatar full-width d-flex justify-center align-center"
        >
          <v-icon>mdi-camera</v-icon>
        </span>
        <input
          ref="uploader"
          class="d-none"
          type="file"
          @change="onFileChanged"
        />
      </v-avatar>
      <v-text-field
        :rules="rules"
        v-model="model"
        :error-messages="errorMessages"
        class="error-input"
      />
    </div>
  </div>
</template>

<style scoped lang="scss">
.picker >>> .v-input__control,
.picker >>> .v-input__slot {
  min-height: 32px !important;
}

.error-input::v-deep {
  padding: 0 5px !important;
  .v-input__slot {
    display: none !important;
  }
}

.new-avatar {
  position: absolute;
  visibility: hidden;
  bottom: 0;
  z-index: 2111111111;
  height: 100%;
  left: 0;
  background-color: var(--v-primary-base);
  color: #fff;
  cursor: pointer;

  &::v-deep {
    .v-image__image--cover {
      background-size: contain !important;
    }
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active до версии 2.1.8 */ {
  opacity: 0;
}

.avatar:hover {
  .display-icon {
    visibility: visible !important;
  }
}

.user-avatar {
  cursor: pointer;
  .user-camera {
    display: none;
  }

  &:hover {
    span:first-child {
      display: none;
    }
    .user-camera {
      display: block;
    }
  }
}
</style>
