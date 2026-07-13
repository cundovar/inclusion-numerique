
import { describe, it, expect } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import InputSearch from '../inputSearch.vue';

const AppInputStub = {
  inheritAttrs: false,
  template:
    '<input @input="$emit(\'update:modelValue\', $event.target.value)" @keyup="$emit(\'keyup\', $event)" />'
};

const AppButtonStub = {
  template: '<button @click="$emit(\'click\')"><slot /></button>'
};

describe('inputSearch', () => {
  it('emit update:modelValue when typing', async () => {
    const wrapper = shallowMount(InputSearch, {
      props: { modelValue: '' },
      global: {
        stubs: { AppInput: AppInputStub, AppButton: AppButtonStub }
      }
    });

    await wrapper.find('input').setValue('hello');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['hello']);
  });

  it('emit search on enter and on click', async () => {
    const wrapper = shallowMount(InputSearch, {
      props: { modelValue: '' },
      global: {
        stubs: { AppInput: AppInputStub, AppButton: AppButtonStub }
      }
    });

    await wrapper.find('input').trigger('keyup', { key: 'Enter' });
    await wrapper.findAllComponents(AppButtonStub)[0].vm.$emit('click');

    expect(wrapper.emitted('search')?.length).toBe(2);
  });
});
